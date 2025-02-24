<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as LoginBase;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;

class Login extends LoginBase
{
    protected ?string $maxWidth = 'xs';

    public function getView(): string
    {
        return 'filament.pages.login';
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getUsernameFormComponent()->default('root'),
                $this->getPasswordFormComponent()->default('password'),
            ])
            ->statePath('data');
    }

    protected function getUsernameFormComponent(): Component
    {
        return TextInput::make('username')
            ->label('Nome de Usuário')
            ->required()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    /**
     * @throws ValidationException
     */
    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.username' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'username' => $data['username'],
            'password' => $data['password'],
        ];
    }
}
