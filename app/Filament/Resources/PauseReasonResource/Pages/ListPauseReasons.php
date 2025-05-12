<?php

namespace App\Filament\Resources\PauseReasonResource\Pages;

use App\Filament\Resources\PauseReasonResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPauseReasons extends ListRecords
{
    protected static string $resource = PauseReasonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
