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

        $roleProducao = Role::firstOrCreate(['name' => 'Produção', 'guard_name' => 'web']);
        $roleVendedor = Role::firstOrCreate(['name' => 'Vendedor', 'guard_name' => 'web']);
        $roleMotorista = Role::firstOrCreate(['name' => 'Motorista', 'guard_name' => 'web']);

        $allPermissions = Permission::pluck('name')->toArray();
        $roleSuperAdmin->syncPermissions($allPermissions);

        $gerentePermissions = Permission::where('name', 'not like', '%force_delete%')
            ->pluck('name')->toArray();
        $roleGerente->syncPermissions($gerentePermissions);

        $roleUsuario->syncPermissions([]);


        $this->call([
            CompanySeeder::class,
        ]);

        $company = Company::first();
        $company2 = Company::skip(1)->first();

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
            'name' => 'Usuário Vendedor',
            'username' => 'vendedor',
            'is_active' => true,
        ])->assignRole($roleVendedor);

        User::factory()->create([
            'company_id' => $company->uuid,
            'name' => 'Usuario Produção',
            'username' => 'producao',
            'is_active' => true,
        ])->assignRole([$roleProducao]);

        User::factory()->create([
            'company_id' => $company->uuid,
            'name' => 'Usuario Motorista',
            'username' => 'motorista',
            'is_active' => true,
        ])->assignRole($roleMotorista);

        User::factory()->create([
            'company_id' => $company2->uuid,
            'name' => 'Usuario de outra Empresa',
            'username' => 'outro',
            'is_active' => true,
        ])->assignRole($roleMotorista);

        $this->call([
            PauseReasonSeeder::class,
            HolidaySeeder::class,
            ClientSeeder::class,

            ChartOfAccountSeeder::class,
            FinancialTransactionSeeder::class,

            ProductSeeder::class,
            ProductionStepSeeder::class,
            ProductionProcessSeeder::class,
            ProductionOrderSeeder::class,

            SalesGoalSeeder::class,
            SalesVisitSeeder::class,
            SalesOrderSeeder::class,

            VehicleSeeder::class
        ]);
    }
}
