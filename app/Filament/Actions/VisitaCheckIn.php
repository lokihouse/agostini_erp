<?php

namespace App\Filament\Actions;

use Closure;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;

class VisitaCheckIn extends Action
{
    protected string | Htmlable | Closure | null $label = '';
    protected string | Closure | null $icon = 'heroicon-o-building-storefront';
    protected string | Closure | null $tooltip = 'Check-in na loja do cliente';

    protected function setUp(): void
    {
        // parent::setUp();
        $this->action(function ($record) {
            dd($record);
            // Redirect::away("https://www.google.com.br/maps/dir/" . $record->cliente->localizacao['lat'] . "," . $record->cliente->localizacao['lng']);
        });
    }
}
