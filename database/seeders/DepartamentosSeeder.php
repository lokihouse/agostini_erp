<?php

namespace Database\Seeders;

use App\Models\Departamento;
use Illuminate\Database\Seeder;

class DepartamentosSeeder extends Seeder
{
    public function run(): void
    {
        Departamento::create([
            'empresa_id' => 1,
            'nome' => 'Departamento 1',
            'descricao' => 'Departamento 1',
        ]);

        Departamento::create([
            'empresa_id' => 2,
            'nome' => 'Departamento 2',
            'descricao' => 'Departamento 2',
        ]);
    }
}
