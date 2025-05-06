<?php

namespace App\Filament\Resources\ProductionStepResource\Pages;

use App\Filament\Resources\ProductionStepResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductionSteps extends ListRecords
{
    protected static string $resource = ProductionStepResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
