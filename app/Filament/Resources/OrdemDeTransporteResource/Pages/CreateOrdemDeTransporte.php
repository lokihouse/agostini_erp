<?php

namespace App\Filament\Resources\OrdemDeTransporteResource\Pages;

use App\Filament\Resources\OrdemDeTransporteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrdemDeTransporte extends CreateRecord
{
    protected static string $resource = OrdemDeTransporteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['empresa_id'] = auth()->user()->empresa_id;
        $data['user_id'] = intval($data['user_id']);
        $data['entregas'] = json_encode($data['entregas'] ?? []);
        $data['rota'] = json_encode($data['rota'] ?? []);
        return parent::mutateFormDataBeforeCreate($data);
    }
}
