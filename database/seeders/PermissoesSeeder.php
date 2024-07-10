<?php

namespace Database\Seeders;

use App\Http\Controllers\RegistroDePontoController;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

class PermissoesSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('shield:generate --all');
        Role::create(['name' => 'gerente']);
    }
}
