<?php

namespace App\Filament\Clusters\Vendas\Resources\VendedorResource\Pages;

use App\Filament\Clusters\Vendas\Resources\VendedorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVendedors extends ListRecords
{
    protected static string $resource = VendedorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
