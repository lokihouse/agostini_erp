<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Company; use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientSeeder extends Seeder
{
    
    public function run(): void
    {
                $company = Company::first();

        if (!$company) {
            $this->command->error('Nenhuma empresa encontrada. Crie uma empresa antes de rodar o ClientSeeder ou ajuste o seeder para criar uma empresa padrão.');
                                                return;         }

        $clientsData = [
            ['name' => 'Petrobras', 'social_name' => 'Petrobras (PETR3/PETR4)', 'tax_number' => '33.000.167/0001-01'],
            ['name' => 'Itaú Unibanco', 'social_name' => 'Itaú Unibanco (ITUB4)', 'tax_number' => '60.872.504/0001-23'],
            ['name' => 'Vale', 'social_name' => 'Vale (VALE3)', 'tax_number' => '33.087.037/0001-01'],
            ['name' => 'Weg', 'social_name' => 'Weg (WEGE3)', 'tax_number' => '89.951.367/0001-79'],
            ['name' => 'Ambev', 'social_name' => 'Ambev (ABEV3)', 'tax_number' => '07.526.557/0001-00'],
            ['name' => 'Banco do Brasil', 'social_name' => 'Banco do Brasil (BBAS3)', 'tax_number' => '00.000.000/0001-91'],
            ['name' => 'Bradesco', 'social_name' => 'Bradesco (BBDC4)', 'tax_number' => '60.746.948/0001-12'],
            ['name' => 'BTG Pactual', 'social_name' => 'BTG Pactual (BPAC11)', 'tax_number' => '30.306.294/0001-45'],
            ['name' => 'Santander Brasil', 'social_name' => 'Santander Brasil (SANB11)', 'tax_number' => '90.400.888/0001-42'],
            ['name' => 'Itaúsa', 'social_name' => 'Itaúsa (ITSA4)', 'tax_number' => '61.532.644/0001-15'],
            ['name' => 'Eletrobras', 'social_name' => 'Eletrobras (ELET3)', 'tax_number' => '00.000.007/0001-81'],
            ['name' => 'JBS', 'social_name' => 'JBS (JBSS3)', 'tax_number' => '02.916.265/0001-60'],
            ['name' => 'Telefônica Brasil', 'social_name' => 'Telefônica Brasil (VIVT3)', 'tax_number' => '02.558.157/0001-62'],
            ['name' => 'Sabesp', 'social_name' => 'Sabesp (SBSP3)', 'tax_number' => '43.776.517/0001-80'],
            ['name' => 'BB Seguridade', 'social_name' => 'BB Seguridade (BBSE3)', 'tax_number' => '17.344.597/0001-94'],
            ['name' => 'B3', 'social_name' => 'B3 (B3SA3)', 'tax_number' => '09.346.601/0001-25'],
            ['name' => 'Suzano', 'social_name' => 'Suzano (SUZB3)', 'tax_number' => '16.404.287/0001-55'],
            ['name' => 'Rede D\'Or São Luiz', 'social_name' => 'Rede D\'Or São Luiz (RDOR3)', 'tax_number' => '06.908.539/0001-69'],
            ['name' => 'Raia Drogasil', 'social_name' => 'Raia Drogasil (RADL3)', 'tax_number' => '61.585.865/0001-51'],
            ['name' => 'Klabin', 'social_name' => 'Klabin (KLBN11)', 'tax_number' => '89.637.490/0001-45'],
        ];

        foreach ($clientsData as $clientData) {
            Client::create([
                'company_id' => $company->uuid,                 'name' => $clientData['name'],
                'social_name' => $clientData['social_name'],
                'tax_number' => preg_replace('/[^0-9]/', '', $clientData['tax_number']),
                'status' => Client::STATUS_ACTIVE,
            ]);
        }
        $this->command->info(count($clientsData) . ' clientes criados para a empresa: ' . $company->name);
    }
}
