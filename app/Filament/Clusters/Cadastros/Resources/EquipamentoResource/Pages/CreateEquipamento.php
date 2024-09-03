<?php

namespace App\Filament\Clusters\Cadastros\Resources\EquipamentoResource\Pages;

use App\Filament\Clusters\Cadastros\Resources\EquipamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEquipamento extends CreateRecord
{
    protected static string $resource = EquipamentoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = parent::mutateFormDataBeforeCreate($data);

        $data['empresa_id'] = auth()->user()->empresa_id;

        return $data;
    }
}
