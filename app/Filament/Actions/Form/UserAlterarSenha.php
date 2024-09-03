<?php

namespace App\Filament\Actions\Form;

use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;

class UserAlterarSenha extends Action
{
    protected string | Htmlable | Closure | null $label = 'Alterar Senha';

    protected function setUp(): void
    {
        parent::setUp();
        $this->modalSubmitActionLabel('Atualizar Senha');
        $this->modalWidth('xs');
        $this->form([
            TextInput::make('password')
                ->label('Nova Senha')
                ->password()
                ->revealable()
                ->required(),
        ]);
        $this->action(function ($data, $record){
            $record->password = Hash::make($data['password']);
            $record->save();
            Notification::make()
                ->title('Senha alterada com sucesso!')
                ->success()
                ->send();
        });
    }
}
