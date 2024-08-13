<?php

namespace Database\Seeders;

use App\Models\OrdemDeProducao;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrdemDeProducaoSeeder extends Seeder
{
    public function run(): void
    {
        OrdemDeProducao::create([
            'empresa_id' => 1,
            'user_id' => 1,
            'status' => 'cancelada',
            'motivo_cancelamento' => 'Cliente desistiu',
            'completude' => 0,
            'previsao_inicio' => null,
            'previsao_final' => null,
            'data_inicio' => null,
            'data_final' => null,
            'produtos' => json_encode([
                [
                    'produto_id' => 1,
                    'quantidade' => 10,
                ],
                [
                    'produto_id' => 2,
                    'quantidade' => 20,
                ],
            ]),
        ]);

        OrdemDeProducao::create([
            'empresa_id' => 1,
            'user_id' => 1,
            'status' => 'finalizada',
            'completude' => 100,
            'previsao_inicio' => Carbon::create(2024,8,10),
            'previsao_final' => Carbon::create(2024,8,12),
            'data_inicio' => Carbon::create(2024,8,10),
            'data_final' => Carbon::create(2024,8,12),
            'produtos' => json_encode([
                [
                    'produto_id' => 1,
                    'quantidade' => 10,
                ],
                [
                    'produto_id' => 2,
                    'quantidade' => 20,
                ],
            ]),
        ]);

        OrdemDeProducao::create([
            'empresa_id' => 1,
            'user_id' => 1,
            'status' => 'em_producao',
            'completude' => 15,
            'previsao_inicio' => Carbon::create(2024,8,10),
            'previsao_final' => Carbon::create(2024,8,14),
            'data_inicio' => Carbon::create(2024,8,10),
            'data_final' => null,
            'produtos' => json_encode([
                [
                    'produto_id' => 1,
                    'quantidade' => 10,
                ],
                [
                    'produto_id' => 2,
                    'quantidade' => 20,
                ],
            ]),
        ]);

        OrdemDeProducao::create([
            'empresa_id' => 1,
            'user_id' => 1,
            'status' => 'agendada',
            'completude' => 0,
            'previsao_inicio' => Carbon::create(2024,8,13),
            'previsao_final' => Carbon::create(2024,8,14),
            'data_inicio' => null,
            'data_final' => null,
            'produtos' => json_encode([
                [
                    'produto_id' => 1,
                    'quantidade' => 10,
                ],
                [
                    'produto_id' => 2,
                    'quantidade' => 20,
                ],
            ]),
        ]);

        OrdemDeProducao::create([
            'empresa_id' => 1,
            'user_id' => 1,
            'status' => 'rascunho',
            'completude' => 0,
            'previsao_inicio' => null,
            'previsao_final' => null,
            'data_inicio' => null,
            'data_final' => null,
            'produtos' => json_encode([
                [
                    'produto_id' => 1,
                    'quantidade' => 10,
                ],
                [
                    'produto_id' => 2,
                    'quantidade' => 20,
                ],
            ]),
        ]);
    }
}
