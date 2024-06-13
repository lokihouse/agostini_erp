<?php

namespace App\Filament\Actions;

use App\Models\Visita;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\MaxWidth;
use Filament\Actions\Action;

class VisitaAgendar extends Action
{
    protected string | Closure | null $defaultView = 'filament-actions::button-action';
    protected MaxWidth | string | Closure | null $modalWidth = 'sm';
    protected string | Closure | null $modalSubmitActionLabel = 'Agendar';
    protected function setUp(): void
    {
        $this->form([
            Select::make('cliente_id')
                ->relationship('cliente', 'nome_fantasia')
                ->searchable()
                ->preload()
                ->columnSpanFull()
                ->required(),
            DatePicker::make('proxima_data')
                ->date()
                ->minDate(now()->format('Y-m-d'))
                ->format('Y-m-d')
                ->required()
                ->columnSpanFull(),
            Select::make('user_id')
                ->relationship('responsavel', 'name', function ($query) {
                    $query->where('empresa_id', auth()->user()->empresa_id);
                })
                ->searchable()
                ->preload()
                ->columnSpanFull()
        ]);

        $this->action(function($data) {
            $model = new Visita();

            $model->empresa_id = auth()->user()->empresa_id;
            $model->cliente_id = $data['cliente_id'];
            $model->user_id = $data['cliente_id'];
            $model->status = 'agendada';
            $model->data = $data['proxima_data'];

            $model->save();
        });
    }
}
