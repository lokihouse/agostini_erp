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

class PedidoGerarOrdemDeProducao extends Action
{
    protected string | Htmlable | Closure | null $label = '';
    protected string | Closure | null $icon = 'heroicon-s-ticket';
    protected string | Closure | null $tooltip = 'Gerar Ordem de Produção';
    protected string | array | Closure | null $color = 'primary';
    protected MaxWidth | string | Closure | null $modalWidth = 'sm';

    protected function setUp(): void
    {
        $this->action(function ($record, $data) {
            dd($record, $data);
        });
    }

    function isHidden(): bool
    {
        return $this->getRecord()->status !== 'confirmado';
    }
}
