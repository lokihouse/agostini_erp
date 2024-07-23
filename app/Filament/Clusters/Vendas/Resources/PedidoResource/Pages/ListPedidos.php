<?php

namespace App\Filament\Clusters\Vendas\Resources\PedidoResource\Pages;

use App\Filament\Clusters\Vendas\Resources\PedidoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPedidos extends ListRecords
{
    protected static string $resource = PedidoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
