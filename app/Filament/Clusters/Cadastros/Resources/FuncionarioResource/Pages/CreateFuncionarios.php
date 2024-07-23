<?php

namespace App\Filament\Clusters\Cadastros\Resources\FuncionarioResource\Pages;

use App\Filament\Clusters\Cadastros\Resources\FuncionarioResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFuncionarios extends CreateRecord
{
    protected static string $resource = FuncionarioResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = parent::mutateFormDataBeforeCreate($data);

        $data['empresa_id'] = auth()->user()->empresa_id;

        return $data;
    }
}
