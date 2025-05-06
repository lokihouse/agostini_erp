<?php

namespace Database\Seeders;

use App\Models\Company; // <-- Importar Company
use App\Models\Product;
use App\Models\ProductionStep;
use App\Models\WorkSlot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
// use Illuminate\Support\Facades\DB; // DB Facade não é estritamente necessário aqui

class ProductionProcessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Obter a empresa para a qual criar os processos
        $company = Company::first();
        if (!$company) {
            $this->command->error('Nenhuma empresa encontrada. Crie uma empresa antes de rodar o ProductionProcessSeeder.');
            return;
        }
        $this->command->info("Usando a empresa: {$company->name} ({$company->uuid}) para vincular processos.");

        // 2. Obter recursos APENAS desta empresa
        $products = Product::where('company_id', $company->uuid)->get();
        $steps = ProductionStep::where('company_id', $company->uuid)->get();
        $slots = WorkSlot::where('company_id', $company->uuid)->get();

        if ($products->isEmpty()) {
            $this->command->warn("Nenhum produto encontrado para a empresa {$company->name}. Rode o ProductSeeder primeiro.");
            // Não retorna aqui, pois ainda pode vincular slots a etapas
        }
        if ($steps->isEmpty()) {
            $this->command->warn("Nenhuma etapa de produção encontrada para a empresa {$company->name}. Rode o ProductionStepSeeder primeiro.");
            return; // Se não há etapas, não há nada para vincular
        }

        // --- Vincular Etapas aos Produtos ---
        if ($products->isNotEmpty()) {
            $this->command->info('Vinculando Etapas aos Produtos...');
            foreach ($products as $product) {
                // Garante que há etapas suficientes para selecionar
                if ($steps->count() < 2) {
                    $this->command->warn("Poucas etapas ({$steps->count()}) para vincular ao produto {$product->name}. Pulando.");
                    continue;
                }
                // Seleciona um número aleatório de etapas (entre 2 e 5) para cada produto
                $stepsToAttach = $steps->random(rand(2, min(5, $steps->count())));
                $order = 1;
                $attachData = []; // Usar array para attach mais eficiente
                foreach ($stepsToAttach as $step) {
                    // Prepara os dados para o attach
                    $attachData[$step->uuid] = ['step_order' => $order++];
                }
                // Usar attach com array para inserir múltiplos registros de uma vez
                $product->productionSteps()->attach($attachData);
            }
            $this->command->info('Etapas vinculadas aos Produtos.');
        } else {
            $this->command->info('Nenhum produto encontrado para vincular etapas.');
        }


        // --- Vincular Slots às Etapas ---
        if ($slots->isNotEmpty()) {
            $this->command->info('Vinculando Slots de Trabalho às Etapas...');
            foreach ($steps as $step) {
                // Garante que há slots suficientes para selecionar
                if ($slots->count() < 1) {
                    $this->command->warn("Nenhum slot para vincular à etapa {$step->name}. Pulando.");
                    continue;
                }
                // Seleciona um número aleatório de slots (entre 1 e 3) para cada etapa
                $slotsToAttach = $slots->random(rand(1, min(3, $slots->count())));
                // Attach pode ser feito diretamente aqui, pois não há dados extras na pivot
                $step->workSlots()->attach($slotsToAttach->pluck('uuid')->all());
            }
            $this->command->info('Slots vinculados às Etapas.');
        } else {
            $this->command->warn('Nenhum Slot de Trabalho encontrado para vincular às etapas.');
        }
    }
}
