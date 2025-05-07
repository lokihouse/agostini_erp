<?php

namespace App\Filament\Resources\TimeClockEntryResource\Pages;

use App\Filament\Resources\TimeClockEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTimeClockEntries extends ListRecords
{
    protected static string $resource = TimeClockEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
