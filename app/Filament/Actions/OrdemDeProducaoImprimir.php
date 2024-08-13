<?php

namespace App\Filament\Actions;

use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;

class OrdemDeProducaoImprimir extends Action
{
    protected string | Htmlable | Closure | null $label = '';
    protected string | Closure | null $icon = 'heroicon-o-printer';
    protected string | Closure | null $tooltip = 'Imprimir Ordem de Produção';
    protected string | array | Closure | null $color = 'primary';
    protected MaxWidth | string | Closure | null $modalWidth = 'sm';

    protected function setUp(): void
    {
        $this->form([
        ]);

        $this->action(function ($record, $data) {
            Notification::make()
                ->title('Ordem Agendada')
                ->success()
                ->send();
        });
    }

    function isHidden(): bool
    {
        return !($this->getRecord()->status === 'agendada' || $this->getRecord()->status === 'em_producao');
    }
}
