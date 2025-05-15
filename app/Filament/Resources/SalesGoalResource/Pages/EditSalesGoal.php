<?php

namespace App\Filament\Resources\SalesGoalResource\Pages;

use App\Filament\Resources\SalesGoalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalesGoal extends EditRecord
{
    protected static string $resource = SalesGoalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
