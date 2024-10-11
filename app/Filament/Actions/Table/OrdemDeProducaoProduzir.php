<?php

namespace App\Filament\Actions\Table;

use App\Models\Departamento;
use App\Models\Equipamento;
use App\Models\ProdutoEtapa;
use Carbon\Carbon;
use Closure;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;
use Symfony\Component\Process\Process;

class OrdemDeProducaoProduzir extends Action
{
    protected string | Htmlable | Closure | null $icon = 'fas-plus-circle';
    protected string | array | Closure | null $color = 'primary';
    protected string | Htmlable | Closure | null $label = '';
    protected string | Closure | null $tooltip = 'Iniciar Produção';

    protected function setUp(): void
    {
        parent::setUp();
        //$this->requiresConfirmation();
        //$this->modalIcon('fas-plus-circle');
        //$this ->modalHeading('Iniciar Ordem de Produção');
        $this->action(function ($data, $record){
            $produtos_com_etapas = [];
            $produtos = json_decode($record->produtos, true);
            foreach ($produtos as $produto) {
                $produtos_com_etapas_item = [...$produto, "etapas" => []];
                $produtoEtapas = ProdutoEtapa::query()->where('produto_id', $produto['produto_id'])->get()->toArray();
                foreach ($produtoEtapas as $produtoEtapa) {
                    $etapasMock = [
                        "descricao" => $produtoEtapa["descricao"],
                        "departamento_origem_id" => $produtoEtapa['departamento_origem_id'],
                        "departamento_origem_nome" => Departamento::query()
                            ->select('nome')
                            ->where('id', $produtoEtapa['departamento_origem_id'])
                            ->first()
                            ->nome,
                        "equipamento_origem_id" => $produtoEtapa['equipamento_origem_id'] ?? null,
                        "equipamento_origem_nome" => $produtoEtapa['equipamento_origem_id'] ? Equipamento::query()
                            ->select('nome')
                            ->where('id', $produtoEtapa['equipamento_origem_id'])
                            ->first()
                            ->nome : null,
                        "departamento_destino_id" => $produtoEtapa['departamento_destino_id'],
                        "departamento_destino_nome" => Departamento::query()
                            ->select('nome')
                            ->where('id', $produtoEtapa['departamento_destino_id'])
                            ->first()
                            ->nome,
                        "equipamento_destino_id" => $produtoEtapa['equipamento_destino_id'] ?? null,
                        "equipamento_destino_nome" => $produtoEtapa['equipamento_destino_id'] ? Equipamento::query()
                            ->select('nome')
                            ->where('id', $produtoEtapa['equipamento_destino_id'])
                            ->first()
                            ->nome : null,
                        "producao" => array_map(function ($prod) use ($produto) {
                            $prod["quantidade"] = intval($produto['quantidade']) * intval($prod["quantidade"]);
                            return $prod;
                        }, json_decode($produtoEtapa['producao'], true)),
                        "finalizada" => false,
                        "tempo_produtivo" => null,
                        "tempo_improdutivo" => null,
                    ];
                    $produtos_com_etapas_item["etapas"][] = $etapasMock;
                }
                $produtos_com_etapas[] = $produtos_com_etapas_item;
            }

            $nodes = [];
            $edges = [];
            foreach ($produtos_com_etapas as $produto_com_etapa) {
                foreach ($produto_com_etapa['etapas'] as $etapa) {
                    $node_origem = $etapa["departamento_origem_id"] . "_" . $etapa["equipamento_origem_id"] ?? "0";
                    $node_destino = $etapa["departamento_destino_id"] . "_" . $etapa["equipamento_destino_id"] ?? "0";
                    $nodes[] = $node_origem;
                    $nodes[] = $node_destino;
                    $edges[] = $etapa["equipamento_origem_id"] . "->" . $etapa["equipamento_destino_id"];
                }
            }
            sort($nodes);
            sort($edges);

            $nodes = array_unique($nodes);
            $edges = array_unique($edges);

            $nodes_per_depto = [];
            foreach ($nodes as $node) {
                $expld = explode("_", $node);
                $nodes_per_depto[$expld[0]][] = $expld[1];
            }

            $digraph = 'digraph G {
            graph[ rankdir="TD" splines="curved" bgcolor="transparent"];
fontname="Arial,sans-serif";
bgcolor="transparent";
outputorder="edgesfirst";
node [fontname="Arial,sans-serif" style=filled];
edge [fontname="Arial,sans-serif"];
            ';
            foreach ($nodes_per_depto as $depto => $node_per_depto) {
                $node_per_depto = array_map(fn ($node) => $node . "[fontsize=8 label=\"" . Equipamento::query()->find($node)->nome  . "\"]", $node_per_depto);
                $digraph .= "subgraph cluster_" . $depto . " { fontsize=10 label=\"" . Departamento::query()->find($depto)->nome . "\"; " . implode("; ", $node_per_depto) ." }\n";
            }
            $digraph .= implode("\n",$edges);
            $digraph .= "\n}";

            $process = Process::fromShellCommandline('echo \'' . $digraph . '\' | dot -Tpng');
            $process->run();

            if (!$process->isSuccessful()) {
                throw new \Exception('Error: ' . $process->getErrorOutput());
            }

            $mapaDeProcesso = "data:image/svg;base64, " . base64_encode($process->getOutput());

            $record->update([
                'status' => 'em_producao',
                'data_inicio_producao' => Carbon::now(),
                'produtos' => $produtos_com_etapas,
                'mapa_de_processo' => $mapaDeProcesso
            ]);

            Notification::make('iniciada')
                ->title('Ordem de Produção iniciada')
                ->success()
                ->send();
        });
    }

    public function isVisible(): bool
    {
        return $this->getRecord()->status === 'agendada';
    }
}
