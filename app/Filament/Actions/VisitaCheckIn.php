<?php

namespace App\Filament\Actions;

use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;

class VisitaCheckIn extends Action
{
    protected string | Htmlable | Closure | null $label = '';
    protected string | Closure | null $icon = 'heroicon-o-building-storefront';
    protected string | Closure | null $tooltip = 'Check-in na loja do cliente';
    protected MaxWidth | string | Closure | null $modalWidth = 'sm';

    protected function setUp(): void
    {
        $this->form([
            Textarea::make('observacao_inicial')
                ->label('Observações iniciais')
                ->columnSpanFull(),
            FileUpload::make('imagem_inicial')
                ->label('Entrada na loja')
                ->image()
                ->imageEditor()
        ]);

        $this->action(function ($record, $data) {
            $record->update([
                'status' => 'iniciada',
                'observacao_inicial' => $data['observacao_inicial'],
                'imagem_inicial' => $data['imagem_inicial'],
                'user_id' => auth()->user()->id,
            ]);
            Notification::make()
                ->title('Visita Inciada')
                ->success()
                ->send();
        });
    }

    function isHidden(): bool
    {
        return !auth()->user()->can('check_in_visita') || $this->getRecord()->status !== 'agendada';
    }
}
