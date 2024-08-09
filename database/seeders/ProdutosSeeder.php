<?php

namespace Database\Seeders;

use App\Http\Controllers\ProdutoController;
use App\Models\Produto;
use App\Models\ProdutoEtapa;
use Illuminate\Database\Seeder;

class ProdutosSeeder extends Seeder
{
    public function run(): void
    {
        $valor_unitario = fake()->randomFloat(2, 50, 1000);
        $produto = Produto::create([
            'empresa_id' => 1,
            'nome' => 'Produto 1',
            'descricao' => 'Descrição do produto 1',
            'valor_minimo' => $valor_unitario * 0.75,
            'valor_unitario' => $valor_unitario,
            'volumes' => json_encode([
                [
                    'descricao' => 'Caixa 1',
                    'largura' => fake()->numberBetween(20,100),
                    'altura' => fake()->numberBetween(20,100),
                    'comprimento' => fake()->numberBetween(50,300),
                    'peso' => fake()->numberBetween(100,1000),
                ],
                [
                    'descricao' => 'Caixa 2',
                    'largura' => fake()->numberBetween(20,100),
                    'altura' => fake()->numberBetween(20,100),
                    'comprimento' => fake()->numberBetween(50,300),
                    'peso' => fake()->numberBetween(100,1000),
                ]
            ])
        ]);

        ProdutoEtapa::create([
            'produto_id' => $produto->id,
            'equipamento_id_origem' => 1,
            'insumos' => json_encode(['1x Insumo 1', '2x Insumo 2']),
            'equipamento_id_destino' => 2,
            'producao' => json_encode(['1x Produto 1']),
            'tempo_producao' => fake()->numberBetween(600, 6000)
        ]);

        ProdutoEtapa::create([
            'produto_id' => $produto->id,
            'equipamento_id_origem' => 2,
            'insumos' => json_encode(['1x Produto 1']),
            'equipamento_id_destino' => 1,
            'producao' => json_encode(['1x Produto 2']),
            'tempo_producao' => fake()->numberBetween(600, 6000)
        ]);

        ProdutoController::generateMapaDeProducao($produto);
        ProdutoController::updateTempoDeProducao($produto);
    }
}
