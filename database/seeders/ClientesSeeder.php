<?php

namespace Database\Seeders;

use App\Http\Controllers\RegistroDePontoController;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

class ClientesSeeder extends Seeder
{
    public function run(): void
    {
        Cliente::create([
            'empresa_id' => 1,
            'cnpj' => '00000000000000',
            'razao_social' => 'Cliente de teste 1 ltda.',
            'nome_fantasia' => 'CliTest1',
            'logradouro' => 'L',
            'numero' => 'N',
            'bairro' => 'B',
            'municipio' => 'M',
            'uf' => 'MG',
            'cep' => '30000000',
            'latitude' => '-19.916667',
            'longitude' => '-43.933333',
        ]);

        Cliente::create([
            'empresa_id' => 1,
            'cnpj' => '00000000000000',
            'razao_social' => 'Cliente de teste 2 ltda.',
            'nome_fantasia' => 'CliTest2',
            'logradouro' => 'L',
            'numero' => 'N',
            'bairro' => 'B',
            'municipio' => 'M',
            'uf' => 'MG',
            'cep' => '30000000',
            'latitude' => '-19.816667',
            'longitude' => '-43.833333',
        ]);

        Cliente::create([
            'empresa_id' => 2,
            'cnpj' => '00000000000000',
            'razao_social' => 'Cliente de teste 3 ltda.',
            'nome_fantasia' => 'CliTest3',
            'logradouro' => 'L',
            'numero' => 'N',
            'bairro' => 'B',
            'municipio' => 'M',
            'uf' => 'MG',
            'cep' => '30000000',
            'latitude' => '-19.916667',
            'longitude' => '-43.933333',
        ]);
    }
}
