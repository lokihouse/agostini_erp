<?php

namespace App\Filament\Clusters\Cadastros\Resources\ProdutoResource\Pages;

use App\Filament\Clusters\Cadastros\Resources\ProdutoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduto extends CreateRecord
{
    protected static string $resource = ProdutoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = parent::mutateFormDataBeforeCreate($data);

        $data['valor_unitario'] = str_replace(',', '.', $data['valor_unitario']);
        $data['empresa_id'] = auth()->user()->empresa_id;

        return $data;
    }
}
