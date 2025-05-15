<?php

namespace App\Filament\Pages;

use App\Models\SalesVisit;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select as FormSelect;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SalesVisitsWithoutOrderReport extends Page implements HasForms
{
    use InteractsWithForms;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected static string $view = 'filament.pages.sales-visits-without-order-report';
    protected static ?string $title = 'Relatório de Visitas Sem Pedido';
    protected static ?string $navigationLabel = 'Visitas Sem Pedido';
    protected static ?string $slug = 'relatorio-visitas-sem-pedido';

    protected static ?string $navigationGroup = 'Vendas'; // Ou 'Relatórios' se preferir
    protected static ?int $navigationSort = 47; // Ajuste conforme sua preferência

    public ?string $start_date = null;
    public ?string $end_date = null;
    public ?string $salesperson_id = null;

    public Collection $reportData;

    public function mount(): void
    {
        $this->start_date = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->end_date = Carbon::now()->endOfMonth()->format('Y-m-d');

        $this->form->fill([
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'salesperson_id' => $this->salesperson_id,
        ]);

        $this->generateReportData();
    }

    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('start_date')
                ->label('Data Finalização (De)')
                ->native(false)
                ->default(Carbon::now()->startOfMonth())
                ->required()
                ->reactive()
                ->maxDate(fn (callable $get) => $get('end_date') ? Carbon::parse($get('end_date')) : now()),
            DatePicker::make('end_date')
                ->label('Data Finalização (Até)')
                ->native(false)
                ->default(Carbon::now()->endOfMonth())
                ->required()
                ->reactive()
                ->minDate(fn (callable $get) => $get('start_date') ? Carbon::parse($get('start_date')) : null)
                ->maxDate(now()),
            FormSelect::make('salesperson_id')
                ->label('Vendedor (Opcional)')
                ->options(
                    User::whereHas('roles', fn(Builder $q) => $q->whereIn('name', ['Vendedor', 'Administrador'])) // Ajuste as roles
                    ->orderBy('name')
                        ->pluck('name', 'uuid')
                        ->all()
                )
                ->searchable()
                ->preload()
                ->nullable()
                ->reactive(),
        ];
    }

    public function submitFilters(): void
    {
        $data = $this->form->getState();
        $this->start_date = Carbon::parse($data['start_date'])->format('Y-m-d');
        $this->end_date = Carbon::parse($data['end_date'])->format('Y-m-d');
        $this->salesperson_id = $data['salesperson_id'];
        $this->generateReportData();
    }

    protected function generateReportData(): void
    {
        $startDate = Carbon::parse($this->start_date)->startOfDay();
        $endDate = Carbon::parse($this->end_date)->endOfDay();

        $query = SalesVisit::query()
            ->with(['client', 'assignedTo']) // Eager load para performance
            ->where('status', SalesVisit::STATUS_COMPLETED)
            ->whereNull('sales_order_id')
            // ->where(function (Builder $q) { // Opcional: exigir que um dos campos de relatório esteja preenchido
            //     $q->whereNotNull('report_reason_no_order')
            //       ->orWhereNotNull('report_corrective_actions');
            // })
            ->whereBetween('visit_end_time', [$startDate, $endDate]); // Filtra pela data de finalização da visita

        if ($this->salesperson_id) {
            $query->where('assigned_to_user_id', $this->salesperson_id);
        }

        $this->reportData = $query->orderBy('visit_end_time', 'desc')->get();
    }

    protected function getHeaderActions(): array
    {
        return []; // Pode adicionar um botão de exportar aqui no futuro
    }
}
