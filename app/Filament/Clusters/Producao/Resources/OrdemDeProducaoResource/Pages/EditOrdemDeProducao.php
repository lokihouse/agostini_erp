<?php

namespace App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource\Pages;

use App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource;
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
        $data["produtos"] = json_decode($data["produtos"], true);
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        foreach ($data["produtos"] as $p_key => $produto) {
            $produto_nome = Produto::query()->find($produto["produto_id"])->nome;
            $data["produtos"][$p_key]["nome"] = $produto_nome;
        }
        $data['produtos'] = json_encode($data['produtos']);
        return $data;
    }
}
