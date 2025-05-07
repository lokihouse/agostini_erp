<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('shield:generate --panel app --all -n');

        $roleSuperAdmin = Role::firstOrCreate(['name' => config('filament-shield.super_admin.name')]);
        $roleGerente = Role::firstOrCreate(['name' => 'Gerente', 'guard_name' => 'web']);
        $roleUsuario = Role::firstOrCreate(['name' => 'Usuário', 'guard_name' => 'web']);

        $allPermissions = Permission::pluck('name')->toArray();
        $roleSuperAdmin->syncPermissions($allPermissions);

        $gerentePermissions = Permission::where('name', 'not like', '%force_delete%')
            ->pluck('name')->toArray();
        $roleGerente->syncPermissions($gerentePermissions);

        $roleUsuario->syncPermissions([]);

        // ----------------------

        $this->call([
            CompanySeeder::class,
        ]);

        $company = Company::first();

        User::factory()->create([
            'company_id' => $company->uuid,
            'name' => 'Super Admin User',
            'username' => 'root',
            'is_active' => true,
        ])->assignRole($roleSuperAdmin);

        User::factory()->create([
            'company_id' => $company->uuid,
            'name' => 'Gerente',
            'username' => 'gerente',
            'is_active' => true,
        ])->assignRole($roleGerente);

        User::factory()->create([
            'company_id' => $company->uuid,
            'name' => 'Usuario Comum',
            'username' => 'usuario',
            'is_active' => true,
        ])->assignRole($roleUsuario);

        $this->call([
            /*
            ProductSeeder::class,
            ProductionStepSeeder::class,
            WorkSlotSeeder::class,
            ProductionProcessSeeder::class,
            ProductionOrderSeeder::class,
            /**/
            // --------------
            WorkShiftSeeder::class,
            HolidaySeeder::class,
            TimeClockEntrySeeder::class
        ]);
    }
}
