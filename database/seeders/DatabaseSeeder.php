<?php

namespace Database\Seeders;

use App\Http\Controllers\ProdutoController;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Equipamento;
use App\Models\Evento;
use App\Models\OrdemDeProducao;
use App\Models\OrdemDeProducaoProduto;
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

        Empresa::factory()
            ->create([
                'active' => true,
            ]);

        User::factory()
            ->create([
                'active' => true,
                'name' => 'Root',
                'username' => 'root'
            ])
            ->syncRoles('super_admin');

        User::factory()
            ->create([
                'active' => true,
                'name' => 'Operador 1',
                'username' => 'op'
            ])
            ->syncRoles('operador');

        Evento::create([
            'nome' => 'Produção',
            'categoria' => 'Produtivo',
        ]);

        Evento::create([
            'nome' => 'Entre Jornadas',
            'categoria' => 'Intervalo',
        ]);

        Evento::create([
            'nome' => 'Entre Turnos',
            'categoria' => 'Intervalo',
        ]);

        Evento::create([
            'nome' => 'Limpeza',
            'categoria' => 'Improdutivo',
        ]);

        Evento::create([
            'nome' => 'Movimentação Interna',
            'categoria' => 'Movimentação ',
        ]);

        Departamento::create([
            'empresa_id' => 1,
            'nome' => 'Departamento 1',
            'descricao' => 'Descrição do departamento 1'
        ]);

        Departamento::create([
            'empresa_id' => 1,
            'nome' => 'Departamento 2',
            'descricao' => 'Descrição do departamento 2'
        ]);

        Departamento::create([
            'empresa_id' => 1,
            'nome' => 'Departamento 3',
            'descricao' => 'Descrição do departamento 3'
        ]);

        Departamento::create([
            'empresa_id' => 1,
            'nome' => 'Departamento 4',
            'descricao' => 'Descrição do departamento 4'
        ]);

        Departamento::create([
            'empresa_id' => 1,
            'nome' => 'Departamento 5',
            'descricao' => 'Descrição do departamento 5'
        ]);

        Equipamento::create([
            'empresa_id' => 1,
            'departamento_id' => 2,
            'nome' => 'Máquina 1',
            'descricao' => 'Descrição da máquina 1'
        ]);

        Equipamento::create([
            'empresa_id' => 1,
            'departamento_id' => 3,
            'nome' => 'Máquina 2',
            'descricao' => 'Descrição da máquina 2'
        ]);

        Equipamento::create([
            'empresa_id' => 1,
            'departamento_id' => 4,
            'nome' => 'Máquina 3',
            'descricao' => 'Descrição da máquina 3'
        ]);

        $valor_venda = fake()->randomFloat(2, 50, 1000);
        $produto = Produto::create([
            'empresa_id' => 1,
            'nome' => 'Produto 1',
            'descricao' => 'Descrição do produto 1',
            'valor_minimo' => $valor_venda * 0.75,
            'valor_venda' => $valor_venda,
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
            'empresa_id' => 1,
            'produto_id' => $produto->id,
            'departamento_origem_id' => 1,
            'departamento_destino_id' => 2,
            'equipamento_destino_id' => 1,
            'tempo' => fake()->numberBetween(10,30),
        ]);
        ProdutoEtapa::create([
            'empresa_id' => 1,
            'produto_id' => $produto->id,
            'departamento_origem_id' => 2,
            'departamento_destino_id' => 3,
            'equipamento_destino_id' => 2,
            'tempo' => fake()->numberBetween(10,30),
        ]);
        ProdutoEtapa::create([
            'empresa_id' => 1,
            'produto_id' => $produto->id,
            'departamento_origem_id' => 3,
            'departamento_destino_id' => 4,
            'equipamento_destino_id' => 3,
            'tempo' => fake()->numberBetween(10,30),
        ]);
        ProdutoEtapa::create([
            'empresa_id' => 1,
            'produto_id' => $produto->id,
            'departamento_origem_id' => 4,
            'departamento_destino_id' => 5,
            'tempo' => fake()->numberBetween(10,30),
        ]);

        $ordemDeProducao = OrdemDeProducao::create([
            'empresa_id' => 1,
            'status' => 'agendada',
            'data_inicio_agendamento' => Carbon::today()->format('Y-m-d'),
            'data_final_agendamento' => Carbon::today()->addDays(fake()->numberBetween(3,7))->format('Y-m-d'),
        ]);

        OrdemDeProducaoProduto::create([
            'ordem_de_producao_id' => $ordemDeProducao->id,
            'produto_id' => $produto->id,
            'quantidade' => 10,
        ]);
    }
}
