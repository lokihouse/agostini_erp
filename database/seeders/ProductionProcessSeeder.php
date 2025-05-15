<?php

namespace Database\Seeders;

use App\Models\Company; use App\Models\Product;
use App\Models\ProductionStep;
use App\Models\WorkSlot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductionProcessSeeder extends Seeder
{
    
    public function run(): void
    {
                $company = Company::first();
        if (!$company) {
            $this->command->error('Nenhuma empresa encontrada. Crie uma empresa antes de rodar o ProductionProcessSeeder.');
            return;
        }
        $this->command->info("Usando a empresa: {$company->name} ({$company->uuid}) para vincular processos.");

                $products = Product::where('company_id', $company->uuid)->get();
        $steps = ProductionStep::where('company_id', $company->uuid)->get();
        $slots = WorkSlot::where('company_id', $company->uuid)->get();

        if ($products->isEmpty()) {
            $this->command->warn("Nenhum produto encontrado para a empresa {$company->name}. Rode o ProductSeeder primeiro.");
                    }
        if ($steps->isEmpty()) {
            $this->command->warn("Nenhuma etapa de produção encontrada para a empresa {$company->name}. Rode o ProductionStepSeeder primeiro.");
            return;         }

                if ($products->isNotEmpty()) {
            $this->command->info('Vinculando Etapas aos Produtos...');
            foreach ($products as $product) {
                                if ($steps->count() < 2) {
                    $this->command->warn("Poucas etapas ({$steps->count()}) para vincular ao produto {$product->name}. Pulando.");
                    continue;
                }
                                $stepsToAttach = $steps->random(rand(2, min(5, $steps->count())));
                $order = 1;
                $attachData = [];                 foreach ($stepsToAttach as $step) {
                                        $attachData[$step->uuid] = ['step_order' => $order++];
                }
                                $product->productionSteps()->attach($attachData);
            }
            $this->command->info('Etapas vinculadas aos Produtos.');
        } else {
            $this->command->info('Nenhum produto encontrado para vincular etapas.');
        }


                if ($slots->isNotEmpty()) {
            $this->command->info('Vinculando Slots de Trabalho às Etapas...');
            foreach ($steps as $step) {
                                if ($slots->count() < 1) {
                    $this->command->warn("Nenhum slot para vincular à etapa {$step->name}. Pulando.");
                    continue;
                }
                                $slotsToAttach = $slots->random(rand(1, min(3, $slots->count())));
                                $step->workSlots()->attach($slotsToAttach->pluck('uuid')->all());
            }
            $this->command->info('Slots vinculados às Etapas.');
        } else {
            $this->command->warn('Nenhum Slot de Trabalho encontrado para vincular às etapas.');
        }
    }
}
