<?php

namespace Database\Seeders;

use App\Http\Controllers\RegistroDePontoController;
use App\Models\User;
use App\Models\Visita;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

class VisitasSeeder extends Seeder
{
    public function run(): void
    {
        for($i=0; $i<10; $i++){
            Visita::create([
                'empresa_id' => 1,
                'cliente_id' => 1,
                'data' => Carbon::make('today')->addDays($i)->format('Y-m-d'),
                'status' => 'agendada'
            ]);

            Visita::create([
                'empresa_id' => 2,
                'cliente_id' => 3,
                'data' => Carbon::make('today')->addDays($i)->format('Y-m-d'),
                'status' => 'agendada'
            ]);
        }
    }
}
