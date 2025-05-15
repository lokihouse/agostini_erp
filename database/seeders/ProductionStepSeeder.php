<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ProductionStep;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductionStepSeeder extends Seeder
{
    
    public function run(): void
    {

        $company = Company::first();

                if (!$company) {
            $this->command->error('Nenhuma empresa encontrada. Crie uma empresa antes de rodar o ProductionStepSeeder.');
            return;         }

        $steps = ['Corte', 'Dobra', 'Usinagem', 'Solda', 'Montagem', 'Inspeção', 'Teste', 'Pintura', 'Acabamento', 'Embalagem'];
        foreach ($steps as $index => $stepName) {
            ProductionStep::factory()
                ->forCompany($company)
                ->create([
                    'name' => $stepName,
                    'default_order' => $index + 1,                 ]);
        }

                    }
}

