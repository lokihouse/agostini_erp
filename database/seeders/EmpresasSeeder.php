<?php

namespace Database\Seeders;

use App\Http\Controllers\RegistroDePontoController;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

class EmpresasSeeder extends Seeder
{
    public function run(): void
    {
        Empresa::create([
            'cnpj' => '00000000000000',
            'razao_social' => 'Empresa 1',
            'nome_fantasia' => 'Emrpesa 1',
            'logradouro' => 'Rua Teste',
            'numero' => '1',
            'bairro' => 'Centro',
            'municipio' => 'Visconde do Rio Branco',
            'uf' => 'MG',
            'cep' => '36520000',
            'latitude' => -21.0091567,
            'longitude' => -42.8417787,
        ]);

        Empresa::create([
            'cnpj' => '00000000000000',
            'razao_social' => 'Empresa 2',
            'nome_fantasia' => 'Emrpesa 2',
            'logradouro' => 'Rua Teste',
            'numero' => '2',
            'bairro' => 'Centro',
            'municipio' => 'Visconde do Rio Branco',
            'uf' => 'MG',
            'cep' => '36520000',
            'latitude' => -21.0091567,
            'longitude' => -42.8417787,
        ]);
    }
}
