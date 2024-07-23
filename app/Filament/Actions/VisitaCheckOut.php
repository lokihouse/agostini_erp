<?php

namespace App\Filament\Actions;

use App\Models\Pedido;
use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;

class VisitaCheckOut extends Action
{
    protected string | Htmlable | Closure | null $label = '';
    protected string | Closure | null $icon = 'heroicon-o-arrow-right-start-on-rectangle';
    protected string | Closure | null $tooltip = 'Check-out do cliente';
    protected string | array | Closure | null $color = 'primary';
    protected MaxWidth | string | Closure | null $modalWidth = 'sm';

    protected function setUp(): void
    {
        $this->form([
            Textarea::make('observacao_final')
                ->label('Observações finais')
                ->columnSpanFull(),
            Placeholder::make('created')
                ->label('')
                ->content('Ao confirmar o pedido, o pedido será enviado para a equipe de produção.'),
            Toggle::make('confirmePedido')
                ->label('Confirmar pedido?')
                ->accepted()
        ]);

        $this->action(function ($record, $data) {
            $pedido = Pedido::query()
                ->where('visita_id', $record->id)
                ->where('status', 'pendente')
                ->first();

            $record->update([
                'status' => 'finalizada',
                'hora_final' => now(),
                'observacao_final' => $data['observacao_final'],
            ]);
            $pedido->update([
                'status' => 'confirmado',
                'confirmacao' => now()
            ]);

            Notification::make()
                ->title('Visita Finalizada')
                ->success()
                ->send();
        });
    }

    function isHidden(): bool
    {
        return !auth()->user()->can('check_in_visita') || $this->getRecord()->status !== 'iniciada';
    }
}
