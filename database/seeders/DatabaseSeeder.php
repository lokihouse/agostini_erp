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
        ]);

        for ($meses=0; $meses < 15; $meses++) {
            $created_at = Carbon::createFromDate(2024,7,1)->subMonths($meses);

            for($clientes = 0; $clientes < fake()->numberBetween(1, 100); $clientes++) {
                $cliente = new Cliente([
                    'empresa_id' => 1,
                    'cnpj' => fake()->cnpj(false),
                    'razao_social' => fake()->company(),
                    'nome_fantasia' => fake()->company(),
                    'logradouro' => fake()->streetName(),
                    'numero' => fake()->buildingNumber(),
                    'bairro' => fake()->streetSuffix(),
                    'municipio' => fake()->city(),
                    'uf' => fake()->state(),
                    'cep' => fake()->postcode(),
                    'latitude' => fake()->latitude(-21.50, -21.60),
                    'longitude' => fake()->longitude(-42, -43),
                    'created_at' => $created_at,
                    'updated_at' => $created_at,
                ]);
                $cliente->save();

                $dataVisita = Carbon::today()->addDays(fake()->numberBetween(-30, 30));
                if($dataVisita->lt(today())) {
                    $visita = new Visita([
                        'empresa_id' => 1,
                        'cliente_id' => $cliente->id,
                        'data' => $dataVisita,
                        'status' => fake()->randomElement(['finalizada', 'cancelada']),
                    ]);
                    $visita->save();
                    $dataVisita = Carbon::today()->addDays(fake()->numberBetween(1, 30));
                }
                $visita = new Visita([
                    'empresa_id' => 1,
                    'cliente_id' => $cliente->id,
                    'data' => $dataVisita,
                    'status' => 'agendada',
                ]);
                $visita->save();
            }
        }
    }
}
