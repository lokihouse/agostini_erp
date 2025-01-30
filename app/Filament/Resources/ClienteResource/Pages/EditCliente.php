<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use App\Utils\Cnpj;
use App\Utils\Telefone;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCliente extends EditRecord
{
    protected static string $resource = ClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['endereco'] = json_decode($data['endereco'], true);
        return parent::mutateFormDataBeforeFill($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['location']);
        $data['cnpj'] = Cnpj::clear($data['cnpj']);
        $data['telefone'] = Telefone::clear($data['telefone']);
        return parent::mutateFormDataBeforeSave($data);
    }
}
