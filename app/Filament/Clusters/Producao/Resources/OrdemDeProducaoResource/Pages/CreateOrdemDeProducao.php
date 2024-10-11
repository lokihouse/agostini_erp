<?php

namespace App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource\Pages;

use App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource;
use App\Models\Produto;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrdemDeProducao extends CreateRecord
{
    protected static string $resource = OrdemDeProducaoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = parent::mutateFormDataBeforeCreate($data);

        $data['empresa_id'] = auth()->user()->empresa_id;
        foreach ($data["produtos"] as $p_key => $produto) {
            $produto_nome = Produto::query()->find($produto["produto_id"])->nome;
            $data["produtos"][$p_key]["nome"] = $produto_nome;
        }
        $data['produtos'] = json_encode($data['produtos']);
        return $data;
    }
}
