<?php

namespace App\Filament\Resources\ProductionStepResource\Pages;

use App\Filament\Resources\ProductionStepResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductionStep extends EditRecord
{
    protected static string $resource = ProductionStepResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
