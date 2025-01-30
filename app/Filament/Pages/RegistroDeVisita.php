<?php

namespace App\Filament\Pages;

use App\Models\PedidoDeVenda;
use App\Models\Visita;
use App\Utils\Cnpj;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;

class RegistroDeVisita extends Page implements HasInfolists
{
    use InteractsWithInfolists;
    protected static bool $shouldRegisterNavigation = false;
    protected ?string $heading = '';
    protected static ?string $slug = 'registro-de-visita/{id}';
    protected static string $view = 'filament.pages.registro-de-visita';

    public static function getRelativeRouteName(): string
    {
        return "registro-de-visita";
    }

    public Visita $record;
    public function mount($id): void
    {
        $this->record = (new Visita())->FindOrFail($id);
    }

    public function clienteInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record->cliente)
            ->schema([
                TextEntry::make('cnpj')->formatStateUsing(fn($state) => Cnpj::format($state)),
                TextEntry::make('razao_social'),
                TextEntry::make('nome_fantasia'),
                TextEntry::make('telefone')->default('-'),
                TextEntry::make('email')->default('-'),
            ]);
    }

    public function cancelamentoInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                TextEntry::make('justificativa'),

            ]);
    }

    public function checkInVisita()
    {
        $this->record->status = "em andamento";
        $this->record->save();
    }
    public function goToPedido()
    {
        if(is_null($this->record->pedido_de_venda_id)){
            $pedido = new PedidoDeVenda();
            $pedido->user_id = auth()->user()->id;
            $pedido->cliente_id = $this->record->cliente_id;
            $pedido->visita_id = $this->record->id;
            $pedido->save();

            $this->record->pedido_de_venda_id = $pedido->id;
            $this->record->save();
        }

        $this->redirect(route('filament.app.pages.pedido-de-venda.registro', $this->record->pedido_de_venda_id));
    }

    public function cancelarVisitaAction(): Action
    {
        return Action::make('test')
            ->label('Cancelar Visita')
            ->color('danger')
            ->requiresConfirmation()
            ->form([
                Textarea::make('justificativa')->required(),
                DatePicker::make('nova_data')
                    ->minDate('tomorrow')
            ])
            ->action(function (array $arguments, $data) {
                $this->record->status = "cancelada";
                $this->record->justificativa = $data['justificativa'];
                $this->record->save();

                if(isset($data['nova_data'])){
                    $novaVisita = $this->record->replicate();
                    $novaVisita->status = "agendada";
                    $novaVisita->justificativa = null;
                    $novaVisita->data = $data['nova_data'];
                    $novaVisita->save();
                }
            });
    }

    public function classeTituloPorStatus()
    {
        switch ($this->record->status) {
            case 'agendada':
                return 'p-2 border border-gray-600 bg-gray-200 text-gray-800';
            case 'em andamento':
                return 'p-2 border border-amber-600 bg-amber-200 text-amber-800';
            case 'realizada':
                return 'p-2 border border-green-600 bg-green-200 text-green-800';
            case 'cancelada':
                return 'p-2 border border-red-600 bg-red-200 text-red-800';
        }
    }
}
