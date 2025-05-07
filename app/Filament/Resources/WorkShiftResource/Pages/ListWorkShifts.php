<?php

namespace App\Filament\Resources\WorkShiftResource\Pages;

use App\Filament\Resources\WorkShiftResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkShifts extends ListRecords
{
    protected static string $resource = WorkShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
