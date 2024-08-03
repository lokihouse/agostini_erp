<?php

namespace App\Filament\Clusters\Vendas\Resources\VendedorResource\Pages;

use App\Filament\Clusters\Vendas\Resources\VendedorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVendedor extends EditRecord
{
    protected static string $resource = VendedorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
