<?php

namespace App\Filament\Clusters\Vendas\Resources\ClienteResource\Pages;

use App\Filament\Clusters\Vendas\Resources\ClienteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClientes extends ListRecords
{
    protected static string $resource = ClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
