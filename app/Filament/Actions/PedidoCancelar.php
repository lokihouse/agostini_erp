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

class PedidoCancelar extends Action
{
    protected string | Htmlable | Closure | null $label = '';
    protected string | Closure | null $icon = 'heroicon-o-archive-box-x-mark';
    protected string | Closure | null $tooltip = 'Cancelar Pedido';
    protected string | array | Closure | null $color = 'danger';
    protected string | Closure | null $modalSubmitActionLabel = 'Registrar';

    protected function setUp(): void
    {
        $this->modalWidth(MaxWidth::ExtraSmall);
        $this->form([
            TextArea::make('observacao')
                ->required()
                ->label('Observação')
                ->placeholder('Descreva o motivo do cancelamento do pedido'),
        ]);

        $this->action(function($record, $data) {
            $record->update([
                'status' => 'cancelado',
                'observacao_cancelamento' => $data['observacao'],
            ]);

            Notification::make()
                ->title('Pedido Cancelado')
                ->success()
                ->send();
        });
    }

    function isHidden(): bool
    {
        return !($this->getRecord()->status === 'confirmado' || $this->getRecord()->status === 'pendente');
    }
}
