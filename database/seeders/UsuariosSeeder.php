<?php

namespace Database\Seeders;

use App\Http\Controllers\RegistroDePontoController;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'empresa_id' => 1,
            'name' => 'Root',
            'username' => 'root',
            'password' => Hash::make('password')
        ])
            ->syncRoles('super_admin');

        User::create([
            'empresa_id' => 1,
            'name' => 'Gerente1',
            'username' => 'gerente1',
            'password' => Hash::make('password')
        ])
            ->syncRoles('gerente');

        User::create([
            'empresa_id' => 2,
            'name' => 'Gerente2',
            'username' => 'gerente2',
            'password' => Hash::make('password')
        ])
            ->syncRoles('gerente');
    }
}
