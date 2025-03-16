<?php

namespace App\Filament\Resources\ProdutoResource\Pages;

use App\Filament\Resources\ProdutoResource;
use App\Models\ProdutoEtapa;
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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['produto_etapas'] = ProdutoEtapa::query()->with(['origens', 'destinos'])->where('produto_id', $data['id'])->get();
        return parent::mutateFormDataBeforeFill($data);
    }
}
