<?php

namespace App\Filament\Resources\PedidoResource\Pages;

use App\Filament\Resources\PedidoDeVendaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPedidoDeVenda extends EditRecord
{
    protected static string $resource = PedidoDeVendaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
