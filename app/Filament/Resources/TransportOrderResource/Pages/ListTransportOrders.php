<?php

namespace App\Filament\Resources\TransportOrderResource\Pages;

use App\Filament\Resources\TransportOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransportOrders extends ListRecords
{
    protected static string $resource = TransportOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
