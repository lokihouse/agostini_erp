<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
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
            'longitude' => fake()->longitude(-42, -43)
        ]);
        $cliente->save();
    }
}
