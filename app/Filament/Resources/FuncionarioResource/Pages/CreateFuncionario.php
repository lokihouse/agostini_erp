<?php

namespace App\Filament\Resources\FuncionarioResource\Pages;

use App\Filament\Resources\FuncionarioResource;
use App\Utils\Cpf;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFuncionario extends CreateRecord
{
    protected static string $resource = FuncionarioResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['empresa_id'] = auth()->user()->empresa_id;
        $data['cpf'] = Cpf::clear($data['cpf']);
        unset($data['roles']);
        return parent::mutateFormDataBeforeCreate($data);
    }
}
