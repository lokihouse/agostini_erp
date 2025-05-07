<?php

namespace App\Filament\Resources\TimeClockEntryResource\Pages;

use App\Filament\Resources\TimeClockEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTimeClockEntry extends EditRecord
{
    protected static string $resource = TimeClockEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
