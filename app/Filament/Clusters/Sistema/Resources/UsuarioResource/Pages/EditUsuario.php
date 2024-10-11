<?php

namespace App\Filament\Clusters\Sistema\Resources\UsuarioResource\Pages;

use App\Filament\Actions\Form\UsuarioAtivar;
use App\Filament\Actions\Form\UsuarioDesativar;
use App\Filament\Clusters\Sistema\Resources\UsuarioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUsuario extends EditRecord
{
    protected static string $resource = UsuarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            UsuarioAtivar::make('ativar'),
            UsuarioDesativar::make('desativar'),
            Actions\DeleteAction::make()->hidden(function($record){
                return !$record->active || Auth::user()->id === $record->id;
            }),
        ];
    }
}
