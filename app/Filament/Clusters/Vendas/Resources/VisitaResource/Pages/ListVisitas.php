<?php

namespace App\Filament\Clusters\Vendas\Resources\VisitaResource\Pages;

use App\Filament\Clusters\Vendas\Resources\VisitaResource;
use App\Models\Visita;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

class ListVisitas extends ListRecords
{
    protected static string $resource = VisitaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Actions\Action::make('Agendar Visita')
                ->modalWidth('sm')
                ->modalSubmitActionLabel('Agendar')
                ->action(function($data) {
                    $model = new Visita();

                    $model->empresa_id = auth()->user()->empresa_id;
                    $model->empresa_id = auth()->user()->empresa_id;
                    $model->cliente_id = $data['cliente_id'];
                    $model->status = 'agendada';
                    $model->data = $data['proxima_data'];

                    $model->save();
                })
                ->form([
                    Select::make('cliente_id')
                        ->relationship('cliente', 'nome_fantasia')
                        ->searchable()
                        ->preload()
                        ->columnSpanFull()
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            /*if(!$get('cliente_id')) return;
                            $cliente = Cliente::query()->where('id', $get('cliente_id'))->first();
                            $set('proxima_visita', Carbon::make($get('data'))->addDays($cliente->recorrencia_de_visitas_dias)->format('Y-m-d'));*/
                        })
                        ->required(),
                    DatePicker::make('proxima_data')
                        ->date()
                        ->minDate(now()->format('Y-m-d'))
                        ->format('Y-m-d')
                        ->required()
                        ->columnSpanFull()
                ])
        ];
    }
}
