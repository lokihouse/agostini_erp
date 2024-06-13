<?php

namespace App\Filament\Actions;

use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
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
        $this->hidden(fn() => !auth()->user()->can('cancelar_visita'));
        $this->modalWidth(MaxWidth::ExtraSmall);
        $this->form([
            Select::make('motivo')
                ->required()
                ->options([
                    'ausente' => 'Cliente Ausente',
                    'desistiu' => 'Cliente Desistiu',
                ]),
            TextArea::make('observacao')
                ->label('Observação')
                ->placeholder('Descreva o motivo da visita cancelada'),
            DatePicker::make('proxima_visita')
                ->label('Reagendar Visita para:')
                ->helperText('Se houver, informe a data da próxima visita')
        ]);

        $this->action(function($record, $data) {
            $record->update([
                'user_id' => auth()->user()->id,
                'status' => 'cancelada',
                'motivo' => $data['motivo'],
                'observacao_cancelamento' => $data['observacao'],
            ]);

            Notification::make()
                ->title('Visita Cancelada')
                ->success()
                ->send();
        });
    }

    function isHidden(): bool
    {
        $a = auth()->user()->can('cancelar_visita');
        $b = auth()->user()->hasRole('super_admin');
        $c = $this->getRecord()->user_id === auth()->user()->id;
        $d = $this->getRecord()->status === 'agendada';
        return (!$a && !$b) || (!$b && !$c) || ($a && !$d);
    }
}
