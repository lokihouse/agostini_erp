<?php

namespace App\Filament\Clusters\Cadastros\Resources\DepartamentoResource\Pages;

use App\Filament\Clusters\Cadastros\Resources\DepartamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartamento extends CreateRecord
{
    protected static string $resource = DepartamentoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = parent::mutateFormDataBeforeCreate($data);

        $data['empresa_id'] = auth()->user()->empresa_id;

        return $data;
    }
}
