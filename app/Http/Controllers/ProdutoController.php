<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Visita;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProdutoController extends Controller
{
    public static function generateMapaDeProducao(Produto $produto) : void
    {
        $etapas = $produto->etapas->toArray();
        if(count($etapas) === 0) {
            $produto->mapa_de_producao = null;
            $produto->save();
            return;
        }
        $etapas_mapped = array_map(function($etapa) {
            return [$etapa["equipamento_id_origem_nome"], $etapa["equipamento_id_destino_nome"], $etapa["tempo_producao"]];
        }, $etapas);

        $digraph = "digraph Producao {  graph [splines=polyline, nodesep=0.75]; node [shape=box, fixedsize=true, width=4, height=0.5]; ";
        foreach ($etapas_mapped as $etapa) {
            $digraph .= "\"{$etapa[0]}\" -> \"{$etapa[1]}\"";
            $label = empty($etapa[2]) ? "-" : ($etapa[2] . "s");
            $digraph .= "[label=\" $label \"]";
            $digraph .= ";";
        }
        $digraph .= "}";

        $response = Http::post('https://quickchart.io/graphviz', [
            'graph' => $digraph,
            'layout' => 'dot',
            'format' => 'png',
        ]);

        $stringToSave = "data:image/png;base64," . base64_encode($response->body());

        $produto->mapa_de_producao = $stringToSave;
        $produto->save();

        self::updateTempoDeProducao($produto);
    }

    public static function updateTempoDeProducao(Produto $produto)
    {
        $etapas = $produto->etapas->toArray();

        if(count($etapas) === 0) return;

        $tempo_de_producao_total = array_reduce($etapas, function($acc, $etapa) use ($produto) {
            $acc += $etapa["tempo_producao"];
            return $acc;
        }, 0);

        $produto->tempo_producao = $tempo_de_producao_total;
        $produto->save();
    }
}
