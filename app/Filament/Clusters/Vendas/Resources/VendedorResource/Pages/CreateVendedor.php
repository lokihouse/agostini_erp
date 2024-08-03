<?php

namespace App\Filament\Clusters\Vendas\Resources\VendedorResource\Pages;

use App\Filament\Clusters\Vendas\Resources\VendedorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateVendedor extends CreateRecord
{
    protected static string $resource = VendedorResource::class;
}
