<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use App\Models\TransportOrder;
use App\Models\TransportOrderItem; // Adicionar importação
use App\Models\User; // Para motoristas
// use App\Models\Vehicle; // Não usado diretamente nos filtros, mas no 'with'
use Carbon\Carbon; // Importar a classe Carbon

class RelatoriosCargas extends Page implements HasForms
{
    use InteractsWithForms;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string $view = 'filament.pages.relatorios-cargas';
    protected static ?string $navigationGroup = 'Cargas';
    protected static ?int $navigationSort = 72;
    protected static ?string $title = 'Relatórios de Cargas';

    public ?string $data_inicio = null;
    public ?string $data_fim = null;
    public ?string $motorista_id = null;
    public ?string $status = null;

    public array $dadosRelatorio = [];

    public function mount(): void
    {
        $this->data_inicio = Carbon::now()->subMonths(6)->format('Y-m-d');
        $this->data_fim = Carbon::now()->format('Y-m-d');

        $this->form->fill([
            'data_inicio' => $this->data_inicio,
            'data_fim' => $this->data_fim,
        ]);

        $this->gerarRelatorio();
    }

    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('data_inicio')
                ->label('Data Início')
                ->reactive()
                ->default(Carbon::now()->subMonths(6)),
            DatePicker::make('data_fim')
                ->label('Data Fim')
                ->reactive()
                ->default(Carbon::now()),
            Select::make('motorista_id')
                ->label('Motorista')
                ->options(User::whereHas('roles', fn($q) => $q->where('name', 'Motorista'))->pluck('name', 'uuid'))
                ->searchable()
                ->reactive(),
            Select::make('status')
                ->label('Status da Ordem')
                ->options(TransportOrder::getStatusOptions())
                ->reactive(),
        ];
    }

    public function gerarRelatorio(): void
    {
        $formData = $this->form->getState();
        $this->data_inicio = $formData['data_inicio'] ?? $this->data_inicio;
        $this->data_fim = $formData['data_fim'] ?? $this->data_fim;
        $this->motorista_id = $formData['motorista_id'] ?? $this->motorista_id;
        $this->status = $formData['status'] ?? $this->status;

        $query = TransportOrder::query();

        if ($this->data_inicio) {
            $query->whereDate('planned_departure_datetime', '>=', $this->data_inicio);
        }
        if ($this->data_fim) {
            $query->whereDate('planned_departure_datetime', '<=', $this->data_fim);
        }
        if ($this->motorista_id) {
            $query->where('driver_id', $this->motorista_id);
        }
        if ($this->status) {
            $query->where('status', $this->status);
        }

        $this->dadosRelatorio = $query->with([
            'driver',
            'vehicle',
            'items' => function ($query) { // Carregar itens com suas relações necessárias
                $query->with(['product', 'client']); // client já era carregado, product é bom garantir
            }
        ])
            ->orderBy('planned_departure_datetime', 'desc')
            ->get()
            ->map(function (TransportOrder $order) {
                $totalItems = $order->items->count();
                $acceptedItemsCount = 0;
                $rejectionReasonsList = [];

                if ($totalItems > 0) {
                    foreach ($order->items as $item) {
                        // Assumindo que TransportOrderItem tem uma constante STATUS_DELIVERED_SUCCESSFULLY
                        // e STATUS_REJECTED, e um campo 'return_reason'
                        if ($item->status === TransportOrderItem::STATUS_COMPLETED) {
                            $acceptedItemsCount++;
                        } elseif ($item->status === TransportOrderItem::STATUS_RETURNED && !empty($item->return_reason)) {
                            $rejectionReasonsList[] = ($item->product?->name ?? 'Item desconhecido') . ": " . $item->return_reason;
                        }
                    }
                    $percentualAceitos = ($acceptedItemsCount / $totalItems) * 100;
                } else {
                    $percentualAceitos = 0;
                }

                return [
                    'uuid' => $order->uuid, // Para o link de detalhes/fotos
                    'numero_ot' => $order->transport_order_number,
                    'status' => $order->status_label,
                    'motorista' => $order->driver?->name,
                    'veiculo' => $order->vehicle?->license_plate, // Veículo
                    'data_saida_prevista' => $order->planned_departure_datetime?->format('d/m/Y H:i'),
                    'data_saida_efetiva' => $order->actual_departure_datetime?->format('d/m/Y H:i'), // Saída efetiva
                    'data_conclusao' => $order->actual_arrival_datetime?->format('d/m/Y H:i'), // Data de conclusão
                    'total_itens' => $totalItems,
                    'percentual_itens_aceitos' => $totalItems > 0 ? number_format($percentualAceitos, 2) . '%' : 'N/A', // Percentual aceitos
                    'justificativas_recusados' => !empty($rejectionReasonsList) ? implode('; ', $rejectionReasonsList) : 'Nenhuma', // Justificativas
                ];
            })->all();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('filtrar')
                ->label('Gerar Relatório')
                ->submit('gerarRelatorio'),
        ];
    }
}
