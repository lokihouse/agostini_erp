<?php

namespace Database\Seeders;

use App\Models\Calendario;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\HorarioDeTrabalho;
use App\Models\JornadaDeTrabalho;
use App\Models\Movimentacao;
use App\Models\PlanoDeConta;
use App\Models\User;
use App\Models\Visita;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('shield:generate --panel app --all -n');
        Role::create(['name' => config('filament-shield.panel_user.name')]);

        Empresa::create([
            'ativo' => true,
            'cnpj' => '00000000000000',
            'razao_social' => 'Empresa de Teste Ltda.',
            'nome_fantasia' => 'TestTech'
        ]);

        User::create([
            'ativo' => true,
            'empresa_id' => 1,
            'username' => 'root',
            'password' => Hash::make('password'),
            'cpf' => '00000000000',
            'nome' => 'Super Usuário'
        ])->syncRoles([config('filament-shield.super_admin.name')]);

        User::create([
            'ativo' => true,
            'empresa_id' => 1,
            'username' => 'a',
            'password' => Hash::make('a'),
            'cpf' => '00000000001',
            'nome' => 'A'
        ])->syncRoles([config('filament-shield.panel_user.name')]);

        $feriados = [
            '2025-01-01' => 'Confraternização Universal',
            '2025-03-04' => 'Carnaval',
            '2025-04-18' => 'Sexta-feira Santa',
            '2025-04-21' => 'Tiradentes',
            '2025-05-01' => 'Dia do Trabalhador',
            '2025-09-07' => 'Independência do Brasil',
            '2025-10-12' => 'Nossa Senhora Aparecida',
            '2025-11-02' => 'Finados',
            '2025-11-15' => 'Proclamação da República',
            '2025-12-25' => 'Natal'
        ];
        foreach($feriados as $data => $nome){
            Calendario::create([
                'data' => $data,
                'tipo' => 'nacional',
                'nome' => $nome
            ]);
        }


        // --------------------------------------------------------------------------

        function generateNearbyCoordinates($baseLatitude, $baseLongitude, $radiusInKm = 5)
        {
            $radiusInDegrees = $radiusInKm / 111;

            $randomLatitude = fake()->randomFloat(6, $baseLatitude - $radiusInDegrees, $baseLatitude + $radiusInDegrees);
            $randomLongitude = fake()->randomFloat(6, $baseLongitude - $radiusInDegrees, $baseLongitude + $radiusInDegrees);

            return [
                'latitude' => $randomLatitude,
                'longitude' => $randomLongitude,
            ];
        }

        for($i=0; $i< 3; $i++){
            $coords = generateNearbyCoordinates(-21.1208,-42.943, 20);
            Cliente::create([
                'empresa_id' => 1,
                'user_id' => 1,
                'cnpj' => fake()->numberBetween(10000000000000, 99999999999999),
                'razao_social' => fake()->company(),
                'nome_fantasia' => fake()->company() . " " . fake()->companySuffix(),
                'latitude' => $coords['latitude'],
                'longitude' => $coords['longitude']
            ]);
        }
    }
}
