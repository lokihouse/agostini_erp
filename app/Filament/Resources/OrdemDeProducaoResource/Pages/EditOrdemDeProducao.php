<?php

namespace App\Filament\Resources\OrdemDeProducaoResource\Pages;

use App\Filament\Resources\OrdemDeProducaoResource;
use App\Models\EventosPorOrdemDeProducao;
use App\Models\ProdutoEtapaDestino;
use App\Models\ProdutoEtapaOrigem;
use App\Models\ProdutoPorOrdemDeProducao;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrdemDeProducao extends EditRecord
{
    protected static string $resource = OrdemDeProducaoResource::class;

    protected function getFormActions(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $produtos = [];
        $produto_etapas = [];

        $_produtos = ProdutoPorOrdemDeProducao::query()->where('ordem_de_producao_id', $data['id'])->get();
        foreach ($_produtos as $produto) {
            $produtos[] = [
                "id" => $produto->id,
                "quantidade" => $produto->quantidade,
                "produto_nome" => $produto->produto->nome,
            ];

            $_etapas = $produto->produto->produto_etapas->toArray();
            foreach ($_etapas as $etapa){
                $_origem = ProdutoEtapaOrigem::query()->where('produto_etapa_id', $etapa['id'])->select('produto_etapa_id_origem')->get()->toArray();
                $_destino = ProdutoEtapaDestino::query()->where('produto_etapa_id', $etapa['id'])->select('produto_etapa_id_destino')->get()->toArray();
                $_etapa = [
                    ...$etapa,
                    "origem" => array_map(fn($e) => $e['produto_etapa_id_origem'], $_origem),
                    "destino" => array_map(fn($e) => $e['produto_etapa_id_destino'], $_destino),
                ];
                $produto_etapas[] = $_etapa;
            }
        }

        $data['produtos'] = $produtos;
        $data['etapas'] = $produto_etapas;
        $data['eventos'] = EventosPorOrdemDeProducao::query()->where('ordem_de_producao_id', $data['id'])->with('responsavel')->get();
        return parent::mutateFormDataBeforeFill($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['produtos']);
        unset($data['eventos']);
        unset($data['etapas']);
        return parent::mutateFormDataBeforeSave($data);
    }
}
