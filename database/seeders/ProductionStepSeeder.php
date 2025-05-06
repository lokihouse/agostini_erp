<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ProductionStep;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductionStepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $company = Company::first();

        // Verificar se uma empresa foi encontrada
        if (!$company) {
            $this->command->error('Nenhuma empresa encontrada. Crie uma empresa antes de rodar o ProductionStepSeeder.');
            return; // Interrompe o seeder
        }

        $steps = ['Corte', 'Dobra', 'Usinagem', 'Solda', 'Montagem', 'Inspeção', 'Teste', 'Pintura', 'Acabamento', 'Embalagem'];
        foreach ($steps as $index => $stepName) {
            ProductionStep::factory()
                ->forCompany($company)
                ->create([
                    'name' => $stepName,
                    'default_order' => $index + 1, // Define uma ordem sequencial
                ]);
        }

        // Cria mais algumas aleatórias se necessário (garantindo nomes únicos)
        // ProductionStep::factory(5)->create(); // Descomente se quiser mais etapas aleatórias
    }
}

