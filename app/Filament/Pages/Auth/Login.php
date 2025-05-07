<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected ?string $maxWidth = 'xs';

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function getView(): string
    {
        return 'filament.pages.login';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('username')
                    ->label('Usuário')
                    ->required()
                    ->autocomplete()
                    ->extraInputAttributes(['tabindex' => 1])
                    ->autofocus(),
                $this->getPasswordFormComponent()
                    ->extraInputAttributes(['tabindex' => 2]),
            ])
            ->statePath('data');
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            return parent::authenticate();
        } catch (ValidationException $e) {
            if (array_key_exists('email', $e->errors())) {
                $errors = $e->errors();
                $errors['username'] = $errors['email'];
                unset($errors['email']);

                throw ValidationException::withMessages($errors);
            }
            throw $e;
        }
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'username' => $data['username'],
            'password' => $data['password'],
        ];
    }
}
