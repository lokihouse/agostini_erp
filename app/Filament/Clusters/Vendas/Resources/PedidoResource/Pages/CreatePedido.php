<?php

namespace App\Filament\Clusters\Vendas\Resources\PedidoResource\Pages;

use App\Filament\Clusters\Vendas\Resources\PedidoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePedido extends CreateRecord
{
    protected static string $resource = PedidoResource::class;
}
