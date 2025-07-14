<?php

namespace App\Filament\Resources\WorkSlotResource\Pages;

use App\Filament\Resources\WorkSlotResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkSlot extends CreateRecord
{
    protected static string $resource = WorkSlotResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()->company_id;
        return $data;
    }
}
