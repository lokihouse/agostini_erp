<?php

namespace App\Filament\Resources\UsuarioResource\Pages;

use App\Filament\Resources\UsuarioResource;
use App\Utils\Cpf;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUsuario extends EditRecord
{
    protected static string $resource = UsuarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['cpf'] = Cpf::clear($data['cpf']);
        unset($data['roles']);
        if(is_null($data['password'])) unset($data['password']);
        return parent::mutateFormDataBeforeSave($data);
    }
}
