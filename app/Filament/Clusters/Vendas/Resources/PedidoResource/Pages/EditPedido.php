<?php

namespace App\Filament\Clusters\Vendas\Resources\PedidoResource\Pages;

use App\Filament\Clusters\Vendas\Resources\PedidoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPedido extends EditRecord
{
    protected static string $resource = PedidoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);

        $valorTotal = null;

        $data['itens_de_pedido'] = (array)json_decode($data['itens_de_pedido']);
        foreach ($data['itens_de_pedido'] as $key => $item) {
            $data['itens_de_pedido'][$key] = (array)$item;
            $valorTotal += $data['itens_de_pedido'][$key]['valor_total'];
        }

        $data['valor_total_pedido'] = $valorTotal;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);

        $record = $this->getRecord();
        $record->itens_de_pedido = $data['itens_de_pedido'];
        $record->save();

        return $data;
    }
}
