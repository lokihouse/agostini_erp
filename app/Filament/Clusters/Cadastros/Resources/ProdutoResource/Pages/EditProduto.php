<?php

namespace App\Filament\Clusters\Cadastros\Resources\ProdutoResource\Pages;

use App\Filament\Clusters\Cadastros\Resources\ProdutoResource;
use App\Models\ProdutoEtapa;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduto extends EditRecord
{
    protected static string $resource = ProdutoResource::class;

    protected $listeners = ['refresh' => '$refresh'];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);

        $data['valor_unitario'] = str_replace('.', ',', $data['valor_unitario']);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = parent::mutateFormDataBeforeSave($data);

        $data['valor_unitario'] = str_replace(',', '.', $data['valor_unitario']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    function deleteEtapa(ProdutoEtapa $etapaId)
    {
        $etapaId->delete();
        $this->dispatch('refresh');
    }
}
