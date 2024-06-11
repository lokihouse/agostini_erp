<?php

namespace App\Filament\Clusters\Cadastros\Resources\ProdutoResource\Pages;

use App\Filament\Clusters\Cadastros\Resources\ProdutoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduto extends EditRecord
{
    protected static string $resource = ProdutoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
