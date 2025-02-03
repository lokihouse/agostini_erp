<?php

namespace App\Filament\Resources\VisitaResource\Pages;

use App\Filament\Resources\VisitaResource;
use App\Models\Cliente;
use App\Models\User;
use App\Models\Visita;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Resources\Pages\ListRecords;

class ListVisitas extends ListRecords
{
    protected static string $resource = VisitaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Actions\Action::make('agendarVisita')
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
                                ->pluck('nome_fantasia', 'id')
                        ),
                    DatePicker::make('data')
                        ->required()
                        ->minDate('today')
                        ->label('Data'),
                ])
                ->action(function($data) {
                    $visita = new Visita();
                    $visita->user_id = Cliente::query()->find($data['cliente'])->user_id;
                    $visita->cliente_id = $data['cliente'];
                    $visita->data = $data[ 'data' ];
                    $visita->status = 'agendada';
                    $visita->save();
                })
        ];
    }
}
