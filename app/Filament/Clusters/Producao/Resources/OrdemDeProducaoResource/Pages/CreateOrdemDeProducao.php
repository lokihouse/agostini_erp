<?php

namespace App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource\Pages;

use App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrdemDeProducao extends CreateRecord
{
    protected static string $resource = OrdemDeProducaoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = parent::mutateFormDataBeforeCreate($data);

        $data['empresa_id'] = auth()->user()->empresa_id;
        $data['user_id'] = auth()->user()->id;
        $data['produtos'] = json_encode($data['produtos']);

        return $data;
    }
}
