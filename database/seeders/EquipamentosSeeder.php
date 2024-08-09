<?php

namespace Database\Seeders;

use App\Models\Departamento;
use App\Models\Equipamento;
use Illuminate\Database\Seeder;

class EquipamentosSeeder extends Seeder
{
    public function run(): void
    {
        Equipamento::create([
            'empresa_id' => 1,
            'departamento_id' => 1,
            'nome' => 'Equipamento 1',
            'descricao' => 'Equipamento 1',
        ]);

        Equipamento::create([
            'empresa_id' => 1,
            'departamento_id' => 1,
            'nome' => 'Equipamento 2',
            'descricao' => 'Equipamento 2',
        ]);

        Equipamento::create([
            'empresa_id' => 2,
            'departamento_id' => 2,
            'nome' => 'Equipamento 2',
            'descricao' => 'Equipamento 2',
        ]);
    }
}
