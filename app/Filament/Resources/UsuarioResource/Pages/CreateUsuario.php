<?php

namespace App\Filament\Resources\UsuarioResource\Pages;

use App\Filament\Resources\UsuarioResource;
use App\Utils\Cpf;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUsuario extends CreateRecord
{
    protected static string $resource = UsuarioResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['cpf'] = Cpf::clear($data['cpf']);
        unset($data['roles']);
        return parent::mutateFormDataBeforeCreate($data);
    }
}
