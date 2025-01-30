<?php

namespace App\Filament\Resources\EmpresaResource\Pages;

use App\Filament\Resources\EmpresaResource;
use App\Utils\Cnpj;
use App\Utils\Telefone;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmpresa extends CreateRecord
{
    protected static string $resource = EmpresaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['cnpj'] = Cnpj::clear($data['cnpj']);
        $data['telefone'] = Telefone::clear($data['telefone']);
        $data['endereco'] = json_encode($data['endereco']);
        return parent::mutateFormDataBeforeCreate($data);
    }
}
