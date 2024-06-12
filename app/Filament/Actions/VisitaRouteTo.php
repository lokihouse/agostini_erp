<?php

namespace App\Filament\Actions;

use Closure;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Redirect;

class VisitaRouteTo extends Action
{
    protected string | Htmlable | Closure | null $label = '';
    protected string | Closure | null $icon = 'heroicon-o-map';
    protected string | Closure | null $tooltip = 'Rota até o cliente';

    protected function setUp(): void
    {
        $this->action(function ($record) {
            Redirect::away("https://www.google.com.br/maps/dir/" . $record->cliente->localizacao['lat'] . "," . $record->cliente->localizacao['lng']);
        });
    }
}
