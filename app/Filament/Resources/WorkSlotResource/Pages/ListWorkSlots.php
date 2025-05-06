<?php

namespace App\Filament\Resources\WorkSlotResource\Pages;

use App\Filament\Resources\WorkSlotResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkSlots extends ListRecords
{
    protected static string $resource = WorkSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
