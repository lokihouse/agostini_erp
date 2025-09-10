<?php

namespace App\Filament\Resources\PricingTableResource\Pages;

use App\Filament\Resources\PricingTableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPricingTable extends ListRecords
{
    protected static string $resource = PricingTableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
