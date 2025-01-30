<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use App\Utils\Cnpj;
use App\Utils\Telefone;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCliente extends CreateRecord
{
    protected static string $resource = ClienteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['empresa_id'] = auth()->user()->empresa_id;
        $data['cnpj'] = Cnpj::clear($data['cnpj']);
        $data['telefone'] = Telefone::clear($data['telefone']);
        $data['endereco'] = json_encode($data['endereco']);
        return parent::mutateFormDataBeforeCreate($data);
    }
}
