<?php

namespace App\Filament\Resources\CalendarioEmpresaResource\Pages;

use App\Filament\Resources\CalendarioEmpresaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCalendarioEmpresa extends CreateRecord
{
    protected static string $resource = CalendarioEmpresaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['empresa_id'] = auth()->user()->empresa_id;
        return parent::mutateFormDataBeforeCreate($data);
    }
}
