<?php

namespace Database\Seeders;

use App\Http\Controllers\RegistroDePontoController;
use App\Models\Empresa;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

class EventosSeeder extends Seeder
{
    public function run(): void
    {
        /*
        Evento::create([
            'nome' => 'Produção',
            'tipo' => 'producao',
            'credito_debito' => 'credito'
        ]);

        Evento::create([
            'nome' => 'Intervalo entre Jornadas',
            'tipo' => 'intervalo',
        ]);

        Evento::create([
            'nome' => 'Intervalo entre Turnos',
            'tipo' => 'intervalo'
        ]);

        Evento::create([
            'nome' => 'Limpeza de máquina',
            'tipo' => 'tempo morto',
            'credito_debito' => 'debito'
        ]);

        Evento::create([
            'nome' => 'Ausência',
            'tipo' => 'tempo morto',
            'credito_debito' => 'debito'
        ]);
        */
    }
}
