<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Evento;
use App\Models\User;
use App\Models\Visita;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

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
            EventosSeeder::class,
            DepartamentosSeeder::class,
            UsuariosSeeder::class,
            ClientesSeeder::class,
            // VisitasSeeder::class,
            // RegistroDePontoSeeder::class
        ]);
    }
}
