<?php

namespace App\Filament\Actions;

use App\Models\Pedido;
use Closure;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Contracts\Support\Htmlable;

class VisitaPedido extends Action
{
    protected string | Htmlable | Closure | null $label = '';
    protected string | Closure | null $icon = 'heroicon-s-ticket';
    protected string | Closure | null $tooltip = 'Ir para Pedido';
    protected string | array | Closure | null $color = 'primary';
    protected MaxWidth | string | Closure | null $modalWidth = 'full';
    protected function setUp(): void
    {
        $this->action(function ($record) {
            $pedido = Pedido::query()->where('visita_id', $record->id)->first();

            if(!!!$pedido){
                $pedido = Pedido::create([
                    'visita_id' => $record->id,
                    'empresa_id' => $record->empresa_id,
                    'status' => 'pendente'
                ]);
                $pedido->save();
            }
            redirect(route('filament.app.vendas.resources.pedidos.preencher', $pedido->id));
        });
    }

    function isHidden(): bool
    {
        return !auth()->user()->can('check_in_visita') || $this->getRecord()->status !== 'iniciada';
    }
}
