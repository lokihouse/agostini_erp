<?php

namespace Database\Seeders;

use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Equipamento;
use App\Models\Evento;
use App\Models\Produto;
use App\Models\ProdutoEtapa;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Artisan::call('shield:generate --all');
        Role::create(['name' => 'operador']);

        Evento::create([
            'nome' => 'Produtivo',
            'categoria' => 'Produtivo',
        ]);

        Evento::create([
            'nome' => 'Intervalo',
            'categoria' => 'Intervalo',
        ]);

        Evento::create([
            'nome' => 'Improdutivo',
            'categoria' => 'Improdutivo',
        ]);

        /* PARA DESENVOLVIMENTO */

        for($e = 0; $e < 1; $e++) {
            $empresa = Empresa::factory()
                ->create([
                    'active' => true,
                ]);

            User::factory()
                ->create([
                    'empresa_id' => $empresa->id,
                    'active' => true,
                    'name' => 'Root',
                    'username' => 'root' . $empresa->id
                ])
                ->syncRoles('super_admin');

            $numeroDeDepartamentos = fake()->randomDigitNotZero()+3;

            for ($i = 0; $i < $numeroDeDepartamentos; $i++) {
                $departamento = Departamento::create([
                    'empresa_id' => $empresa->id,
                    'nome' => 'Departamento ' . $i,
                    'descricao' => 'Descrição do departamento ' . $i
                ]);

                $numeroDeEquipamentos = fake()->randomDigitNotZero()+3;

                for ($j = 0; $j < $numeroDeEquipamentos; $j++) {
                    Equipamento::create([
                        'empresa_id' => $empresa->id,
                        'departamento_id' => $departamento->id,
                        'nome' => 'Máquina ' . $j,
                        'descricao' => 'Descrição da máquina ' . $departamento->id . ".$j"
                    ]);
                }
            }

            $numeroDeProdutos = 0; // fake()->randomDigitNotZero();

            for ($i = 0; $i < $numeroDeProdutos; $i++) {
                $valor_venda = fake()->randomFloat(2, 50, 1000);
                $numeroDeVolumes = fake()->randomDigitNotZero();
                $volumes = [];
                for ($j = 0; $j < $numeroDeVolumes; $j++) {
                    $volumes[] = [
                        'descricao' => 'Caixa ' . $j,
                        'quantidade' => fake()->randomDigitNotZero(),
                        'largura' => fake()->numberBetween(20,100),
                        'altura' => fake()->numberBetween(20,100),
                        'comprimento' => fake()->numberBetween(50,300),
                        'peso' => fake()->numberBetween(100,1000),
                    ];
                }

                $produto = Produto::create([
                    'empresa_id' => $empresa->id,
                    'nome' => 'Produto ' . $i,
                    'descricao' => 'Descrição do produto ' . $i,
                    'valor_minimo' => $valor_venda * 0.75,
                    'valor_venda' => $valor_venda,
                    'volumes' => json_encode($volumes)
                ]);

                $numeroDeEtapasDeProduto = fake()->randomDigitNotZero()+5;
                for($j = 0; $j < $numeroDeEtapasDeProduto; $j++) {
                    $departamentoOrigem = fake()->numberBetween(1, $numeroDeDepartamentos);
                    $equipamentoOrigem = Equipamento::query()->where('departamento_id', $departamentoOrigem)->inRandomOrder()->first()->id;
                    $departamentoDestino = fake()->numberBetween(1, $numeroDeDepartamentos);
                    $equipamentoDestino = Equipamento::query()->where('departamento_id', $departamentoDestino)->inRandomOrder()->first()->id;

                    $producao = [];
                    $numeroDeSubProdutos = fake()->randomDigitNotZero();
                    for ($k = 0; $k < $numeroDeSubProdutos; $k++) {
                        $producao[] = [
                            "descricao" => "Subproduto " . $produto->id . ".$k",
                            "quantidade" => fake()->randomDigitNotZero(),
                        ];
                    }

                    ProdutoEtapa::create([
                        'empresa_id' => $empresa->id,
                        'produto_id' => $produto->id,
                        'departamento_origem_id' => $departamentoOrigem,
                        'departamento_destino_id' => $departamentoDestino,
                        'equipamento_origem_id' => $equipamentoOrigem,
                        'equipamento_destino_id' => $equipamentoDestino,
                        'descricao' => "Você vai pegar os insumos em $departamentoOrigem.$equipamentoOrigem e levar para $departamentoDestino.$equipamentoDestino",
                        'producao' => json_encode($producao),
                        'tempo' => fake()->numberBetween(10,30),
                    ]);
                }
            }
        }
    }
}
