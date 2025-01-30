<?php

namespace App\Filament\Resources\FuncionarioResource\Pages;

use App\Filament\Resources\FuncionarioResource;
use App\Utils\Cpf;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFuncionario extends EditRecord
{
    protected static string $resource = FuncionarioResource::class;

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
