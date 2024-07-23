<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\MovimentacaoFinanceira;
use App\Models\PlanoDeConta;
use Illuminate\Database\Seeder;

class MovimentacaoFinanceiraSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Empresa::all() as $empresa){
            $planoDeConta = PlanoDeConta::query()
                ->where('empresa_id', $empresa->id)
                ->get()
                ->toArray();

            foreach ($planoDeConta as $conta){
                $codigoParts = explode(".", $conta['codigo']);
                if(count($codigoParts) <= 2) continue;
                for($i = 0; $i < 5; $i++){
                    MovimentacaoFinanceira::create([
                        'empresa_id' => $empresa->id,
                        'plano_de_conta_id' => $conta['id'],
                        'descricao' => 'Movimentação de teste',
                        'natureza' => fake()->randomElement(['credito', 'debito']),
                        'valor' => fake()->randomFloat(2, 0, 1000),
                    ]);
                }
            }
        }
    }
}
