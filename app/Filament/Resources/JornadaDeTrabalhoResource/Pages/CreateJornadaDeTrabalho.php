<?php

namespace App\Filament\Resources\JornadaDeTrabalhoResource\Pages;

use App\Filament\Resources\JornadaDeTrabalhoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateJornadaDeTrabalho extends CreateRecord
{
    protected static string $resource = JornadaDeTrabalhoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['empresa_id'] = auth()->user()->empresa_id;
        return parent::mutateFormDataBeforeCreate($data);
    }
}
