<?php

namespace App\Filament\Resources\SalesGoalResource\Pages;

use App\Filament\Resources\SalesGoalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSalesGoals extends ListRecords
{
    protected static string $resource = SalesGoalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
