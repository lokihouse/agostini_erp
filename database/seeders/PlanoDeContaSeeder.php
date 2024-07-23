<?php

namespace Database\Seeders;

use App\Http\Controllers\PlanoDeContaController;
use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanoDeContaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Empresa::all() as $empresa){
            PlanoDeContaController::criarPlanoDeConta($empresa, Carbon::today(), Carbon::today()->addYear());
        }
    }
}
