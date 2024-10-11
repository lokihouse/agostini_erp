<?php

namespace App\Filament\Actions\Form;

use Closure;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class UsuarioDesativar extends Action
{
    protected string | Htmlable | Closure | null $label = 'Desativar';
    protected string | array | Closure | null $color = 'gray';

    protected function setUp(): void
    {
        parent::setUp();
        $this->requiresConfirmation();
        $this->action(function($record){
            $record->active = false;
            $record->save();
            Notification::make('saved')
                ->title('Usuário desativado com sucesso!')
                ->success()
                ->send();
        });
        $this->hidden(function($record){
            return !$record->active || Auth::user()->id === $record->id;
        });
    }
}
