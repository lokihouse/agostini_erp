<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\PlanoDeConta;
use App\Models\Produto;
use App\Models\Visita;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class PlanoDeContaController extends Controller
{
    public static function criarPlanoDeConta(Empresa $empresa, Carbon $dataInicio, Carbon $dataFim)
    {
        $planoDeContas = new PlanoDeConta();
        $planoDeContas->empresa_id = $empresa->id;
        $planoDeContas->codigo = '0';
        $planoDeContas->descricao = 'Plano de Contas - ' . $dataInicio->format('d/m/Y') . ' à ' . $dataFim->format('d/m/Y');
        $planoDeContas->status = false;
        $planoDeContas->data_inicio = $dataInicio;
        $planoDeContas->data_fim = $dataFim;
        $planoDeContas->save();

        $arvoreDeContas = [
            ['Ativos', [
                ['Ativos Circulantes', [
                    ['Caixa', []],
                    ['Bancos', []],
                    ['Contas a Receber', []],
                    ['Estoques', []],
                    ['Despesas Antecipadas', []],
                ]],
                ['Ativos Não Circulantes', [
                    ['Investimentos', []],
                    ['Imobilizado', []],
                    ['Intangível', []]
                ]]
            ]],
            ['Passivos', [
                ['Passivos Circulantes', [
                    ['Contas a Pagar', []],
                    ['Empréstimos e Financiamentos de Curto Prazo', []],
                    ['Salários', []],
                    ['Obrigações Fiscais e Trabalhistas', []]
                ]],
                ['Passivos Não Circulantes', [
                    ['Empréstimos e Financiamentos de Longo Prazo', []],
                    ['Provisões', []],
                    ['Impostos Diferidos', []]
                ]]
            ]],
            ['Patrimônio Líquido', [
                ['Capital Social', []],
                ['Reservas de Lucros', []],
                ['Ações em Tesouraria', []]
            ]],
            ['Receitas', [
                ['Receitas Operacionais', [
                    ['Vendas de Produtos', []],
                    ['Vendas de Serviços', []],
                ]],
                ['Receitas Não Operacionais', [
                    ['Ganhos de Capital', []],
                    ['Receitas Financeiras', []]
                ]]
            ]],
            ['Despesas', [
                ['Despesas Operacionais', [
                    ['Custos de Produção', []],
                    ['Despesas Administrativas', []],
                    ['Despesas Comerciais', []],
                    ['Despesas Financeiras', []]
                ]],
                ['Despesas Não Operacionais', [
                    ['Perdas de Capital', []],
                    ['Despesas Financeiras', []]
                ]]
            ]],
            ['Outras', [
                ['Outras Receitas', []],
                ['Outras Despesas', []]
            ]]
        ];

        foreach ($arvoreDeContas as $conta) {
            $descricao = $conta[0];
            $subContas = $conta[1] ?? null;
            self::criarConta($planoDeContas, $descricao, $subContas);
        }
    }

    public static function getNextCodigo(PlanoDeConta $planoDeConta)
    {
        $planos = PlanoDeConta::query()
            ->select('codigo')
            ->where('plano_de_conta_id', $planoDeConta->id)
            ->get()
            ->toArray();

        if($planoDeConta->codigo == "0"){
            if(empty($planos)){ return "1"; }
            $planos = array_column($planos, 'codigo');
            $last = intval(array_reduce($planos, function($carry, $item){
                $parts = explode(".", $item);
                $last = array_pop($parts);
                return $last > $carry ? $last : $carry;
            }, 0)) + 1;
            return $last;
        }

        if(empty($planos)){
            $zeros = count(explode(".",$planoDeConta->codigo));
            return $planoDeConta->codigo . "." . str_repeat("0", $zeros) . "1";
        }

        $planos = array_column($planos, 'codigo');
        $last = intval(array_reduce($planos, function($carry, $item){
            $parts = explode(".", $item);
            $last = array_pop($parts);
            return $last > $carry ? $last : $carry;
        }, 0)) + 1;

        $zeros = count(explode(".",$planoDeConta->codigo)) + 1;
        return $planoDeConta->codigo . "." . str_pad(''.$last, $zeros, "0", STR_PAD_LEFT);
    }

    private static function criarConta(PlanoDeConta $planoDeContas, string $descricao, mixed $subContas = null)
    {
        $conta = new PlanoDeConta([
            'empresa_id' => $planoDeContas->empresa_id,
            'plano_de_conta_id' => $planoDeContas->id,
            'codigo' => self::getNextCodigo($planoDeContas),
            'movimentacao' => empty($subContas),
            'valor_projetado' => empty($subContas) ? fake()->randomFloat(2,-999999, 999999) : null,
            'descricao' => $descricao,
        ]);
        $conta->save();

        if($subContas) {
            foreach ($subContas as $subConta){
                $descricao = $subConta[0];
                $subContas = $subConta[1] ?? null;
                self::criarConta($conta, $descricao, $subContas);
            }
        }
    }

    public static function ativarPlanoDeConta(PlanoDeConta $planoDeConta)
    {
        self::chagetStatus($planoDeConta, true);
    }

    public static function desativarPlanoDeConta(PlanoDeConta $planoDeConta)
    {
        self::chagetStatus($planoDeConta, false);
    }

    private static function chagetStatus(PlanoDeConta $planoDeConta, bool $status)
    {
        $planoDeConta->status = $status;
        $planoDeConta->save();

        if($planoDeConta->planoDeContasFilhos){
            foreach ($planoDeConta->planoDeContasFilhos as $planoDeContaFilho){
                self::chagetStatus($planoDeContaFilho, $status);
            }
        }
    }

    private static function atualizaValorRecursivo(PlanoDeConta $planoDeConta, string $columnName, float $valor)
    {
        $planoDeConta->$columnName = $planoDeConta->$columnName + $valor;
        $planoDeConta->save();

        if($planoDeConta->planoDeContaPai){
            self::atualizaValorRecursivo($planoDeConta->planoDeContaPai, $columnName, $valor);
        }
    }

    public static function atualizarValorProjetado(PlanoDeConta $planoDeConta)
    {
        if($planoDeConta->planoDeContaPai && $planoDeConta->planoDeContaPai->codigo !== "0"){
            self::atualizaValorRecursivo($planoDeConta->planoDeContaPai, 'valor_projetado', $planoDeConta->valor_projetado);
        }
    }
}
