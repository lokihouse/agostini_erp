<?php

namespace App\Filament\Clusters\Cadastros\Resources\ProdutoResource\Pages;

use App\Filament\Clusters\Cadastros\Resources\ProdutoResource;
use App\Http\Controllers\ProdutoController;
use App\Models\Produto;
use App\Utils\NumberHelper;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduto extends EditRecord
{
    protected static string $resource = ProdutoResource::class;

    protected $listeners = ['refresh'=>'refreshForm'];
    public function refreshForm()
    {
        $this->fillForm();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);

        $data['valor_minimo'] = NumberHelper::fromMoney($data['valor_minimo']);
        $data['valor_venda'] = NumberHelper::fromMoney($data['valor_venda']);
        $data['volumes'] = json_decode($data['volumes'], true);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = parent::mutateFormDataBeforeSave($data);

        $data['valor_minimo'] = NumberHelper::toMoney($data['valor_minimo']);
        $data['valor_venda'] = NumberHelper::toMoney($data['valor_venda']);
        $data['volumes'] = json_encode($data['volumes']);

        return $data;
    }
}
