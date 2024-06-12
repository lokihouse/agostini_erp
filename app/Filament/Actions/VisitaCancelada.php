<?php

namespace App\Filament\Actions;

use Closure;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Redirect;

class VisitaCancelada extends Action
{
    protected string | Htmlable | Closure | null $label = '';
    protected string | Closure | null $icon = 'heroicon-o-archive-box-x-mark';
    protected string | Closure | null $tooltip = 'Cancelar visita';
    protected string | array | Closure | null $color = 'danger';
    protected string | Closure | null $modalSubmitActionLabel = 'Registrar';

    protected function setUp(): void
    {
        $this->modalWidth(MaxWidth::ExtraSmall);
        $this->form([
            Select::make('motivo')
                ->required()
                ->options([
                    'cliente ausente' => 'Cliente Ausente',
                    'cliente desistiu' => 'Cliente Desistiu',
                ])
        ]);

        $this->action(function($record) {
            $record->update([
                'status' => 'cancelada',
                // 'motivo' => $this->motivo,
            ]);
        });
    }
}
