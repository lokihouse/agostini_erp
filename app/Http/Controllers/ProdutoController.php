<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Equipamento;
use App\Models\Produto;
use App\Utils\DateHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

class ProdutoController extends Controller
{

    static function getEtapasMapeadas(Produto $produto)
    {
        return $produto->produto_etapas->map(function ($etapa) {
            return [
                'departamento_origem' => [$etapa->departamento_origem->id, $etapa->departamento_origem->nome ],
                'equipamento_origem' => $etapa->equipamento_origem ? [$etapa->equipamento_origem->id, $etapa->equipamento_origem->nome] : null,
                'departamento_destino' => [$etapa->departamento_destino->id, $etapa->departamento_destino->nome],
                'equipamento_destino' => $etapa->equipamento_destino ? [$etapa->equipamento_destino->id, $etapa->equipamento_destino->nome] : null,
            ];
        });
    }

    static function getDiagraph($etapasMapeadas){
        $departamentos = [];
        $edge = [];

        foreach ($etapasMapeadas as $etapa){
            $dt_key = $etapa['departamento_origem'][0];
            if(!array_key_exists($dt_key, $departamentos)) {
                $departamentos[$dt_key] = [
                    'id' => $dt_key,
                    'nome' => $etapa['departamento_origem'][1],
                    'equipamentos' => []
                ];
            }
            $eq_key = $etapa['equipamento_origem'] ? $etapa['equipamento_origem'][0] : null;
            if(!is_null($etapa['equipamento_origem']) && !array_key_exists($eq_key, $departamentos[$dt_key]['equipamentos'])) {
                $departamentos[$dt_key]['equipamentos'][$eq_key] = [
                    'id' => $eq_key,
                    'nome' => $etapa['equipamento_origem'][1]
                ];
            }

            $dt_key = $etapa['departamento_destino'][0];
            if(!array_key_exists($dt_key, $departamentos)) {
                $departamentos[$dt_key] = [
                    'id' => $dt_key,
                    'nome' => $etapa['departamento_destino'][1],
                    'equipamentos' => []
                ];
            }
            $eq_key = $etapa['equipamento_destino'] ? $etapa['equipamento_destino'][0] : null;
            if(!is_null($etapa['equipamento_destino']) && !array_key_exists($eq_key, $departamentos[$dt_key]['equipamentos'])) {
                $departamentos[$dt_key]['equipamentos'][$eq_key] = [
                    'id' => $eq_key,
                    'nome' => $etapa['equipamento_destino'][1]
                ];
            }

            $out = "d" . $etapa['departamento_origem'][0];

            if(!is_null($etapa['equipamento_origem'])) {
                $out .= ":eq" . $etapa['equipamento_origem'][0] . ":se";
            }else{
                $out .= ":se";
            }

            $out.= " -> d" . $etapa['departamento_destino'][0];

            if(!is_null($etapa['equipamento_destino'])) {
                $out .= ":eq" . $etapa['equipamento_destino'][0] . ":ne";
            }else{
                $out .= ":ne";
            }

            $edge[] = $out;

        }

        $departamentos_mapped = [];

        foreach ($departamentos as $departamento_id => $departamento) {
            $out = "d" . $departamento_id . " [label=\"<dt" . $departamento_id .">". $departamento['nome'];

            if(count($departamento['equipamentos']) > 0) {
                $out .= "|{";
                $equipamentos_count = count($departamento['equipamentos']);
                foreach ($departamento['equipamentos'] as $equipamento_id => $equipamento) {
                    $out .= "<eq" . $equipamento_id . ">" . $equipamento['nome'] . "|";
                    $equipamentos_count--;
                    if($equipamentos_count == 0) {
                        $out = substr($out, 0, -1);
                    }
                }
                $out .= "}";
            }

            $out .=  "\"]";

            $departamentos_mapped[] = $out;
        }

        $diagraph = 'digraph G {
graph[ rankdir="TD" splines="curved" bgcolor="transparent"];
fontname="Arial,sans-serif";
bgcolor="transparent";
outputorder="edgesfirst";
node [fontname="Arial,sans-serif" style=filled];
edge [fontname="Arial,sans-serif"];
node[shape=record];
' . implode("; ",$departamentos_mapped)  . ';
' . implode("; ",$edge)  . ';
}';

        $diagraph = str_replace(array("\r", "\n", "\t"), '', $diagraph);

        return $diagraph;
    }

    static function runDotCommand($diagraph)
    {
        $process = Process::fromShellCommandline('echo \'' . $diagraph . '\' | dot -Tpng');
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception('Error: ' . $process->getErrorOutput());
        }

        return "data:image/svg;base64, " . base64_encode($process->getOutput());
    }
    static function generateMapaDeProducaoWithGraphviz(Produto $produto)
    {
        $etapasMapeadas = self::getEtapasMapeadas($produto);
        if(count($etapasMapeadas) == 0) {
            return null;
        }
        $diagraph = self::getDiagraph($etapasMapeadas);
        $data = self::runDotCommand($diagraph);
        return $data;
    }
    static function updateMapaDeProducaoWithGraphviz(Produto $produto)
    {
        $produto->mapa_de_producao = self::generateMapaDeProducaoWithGraphviz($produto);
        $produto->save();
    }
}
