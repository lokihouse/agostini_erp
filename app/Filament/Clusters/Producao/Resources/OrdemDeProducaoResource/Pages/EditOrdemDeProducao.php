<?php

namespace App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource\Pages;

use App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource;
use App\Http\Controllers\ProdutoController;
use App\Models\OrdemDeProducao;
use App\Models\Produto;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrdemDeProducao extends EditRecord
{
    protected static string $resource = OrdemDeProducaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);
        $ordemDeProducao = OrdemDeProducao::query()->find($data['id']);
        $etapas = [];

        foreach ($ordemDeProducao->produtos_na_ordem as $s){
            if(empty($s['produto_id'])) continue;
            $etapas = array_merge($etapas, ProdutoController::getEtapasMapeadas(Produto::query()->find($s['produto_id']))->toArray());
        }

        $etapas = array_unique($etapas, SORT_REGULAR);

        $diagraph = ProdutoController::getDiagraph($etapas);
        $imagem = ProdutoController::runDotCommand($diagraph);

        $data['mapa_producao'] = $imagem;

        return $data;
    }
}
