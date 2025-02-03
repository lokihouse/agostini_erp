<?php

namespace App\Filament\Widgets;

use App\Models\Cliente;
use App\Models\Visita;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Widget;

class MapaDeVisitas extends Widget implements HasForms, HasActions
{
    use HasWidgetShield;
    use InteractsWithActions;
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.mapa-de-visitas';

    public function goToPage($rota)
    {
        redirect($rota);
    }

    public function agendarVisitaAction(): Action
    {
        return Action::make('agendarVisita')
            ->extraAttributes(['class' => 'rounded-b-none'])
            ->label('Agendar Nova Visita')
            ->requiresConfirmation()
            ->modalDescription(null)
            ->modalIcon('heroicon-o-calendar')
            ->form([
                Select::make('cliente')
                    ->label('Cliente')
                    ->required()
                    ->searchable()
                    ->options(
                        Cliente::query()
                            ->where('user_id', auth()->user()->id)
                            ->pluck('nome_fantasia', 'id')
                    ),
                DatePicker::make('data')
                    ->minDate('today')
                    ->required()
                    ->label('Data'),
            ])
            ->action(function($data) {
                $visita = new Visita();
                $visita->user_id = auth()->user()->id;
                $visita->cliente_id = $data['cliente'];
                $visita->data = $data[ 'data' ];
                $visita->status = 'agendada';
                $visita->save();
            });
    }
}
