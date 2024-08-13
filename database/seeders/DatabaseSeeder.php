<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Visita;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissoesSeeder::class,
            EmpresasSeeder::class,
            UsuariosSeeder::class,
            EventosSeeder::class,

            DepartamentosSeeder::class,
            EquipamentosSeeder::class,
            ProdutosSeeder::class,

            // OrdemDeProducaoSeeder::class,

            ClienteSeeder::class,
        ]);
    }
}
