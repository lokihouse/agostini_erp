<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductionLog;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\ProductionStep;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductionOrderSeeder extends Seeder
{

    public function run(): void
    {
        $company = Company::first();
        if (!$company) {
            $this->command->error('Nenhuma empresa encontrada. Crie uma empresa antes de rodar o ProductionOrderSeeder.');
            return;
        }
        $this->command->info("Usando a empresa: {$company->name} ({$company->uuid})");

        $products = Product::where('company_id', $company->uuid)->get();
        if ($products->isEmpty()) {
            $this->command->warn("Nenhum produto encontrado para a empresa {$company->name}. Rode o ProductSeeder para esta empresa primeiro.");
            return;
        }

        $productionSteps = ProductionStep::where('company_id', $company->uuid)->get();
        if ($productionSteps->isEmpty()) {
            $this->command->warn("Nenhuma etapa de produção encontrada para a empresa {$company->name}. Rode o ProductionStepSeeder para esta empresa primeiro.");
            return;
        }

        $user = User::where('company_id', $company->uuid)->first();
        if (!$user) {
            $this->command->warn("Nenhum usuário encontrado para a empresa {$company->name}. Criando um usuário padrão.");
            $user = User::factory()->for($company)->create();
        }


        $this->command->info('Criando Ordens de Produção, Itens e Logs...');

        ProductionOrder::factory(10)
            ->for($company)->for($user)->has(
                ProductionOrderItem::factory()
                    ->count(1)
                    ->for($company)->state(function (array $attributes, ProductionOrder $order) use ($products, $productionSteps) {
                        $product = $products->whereNotIn('uuid', $order->items->pluck('product_uuid'))->random();

                        $stepUuid = null;
                        $productSteps = $product->productionSteps()->orderBy('pivot_step_order')->get();
                        if ($productSteps->isNotEmpty()) {
                            $stepUuid = $productSteps->first()->uuid;
                        } else {
                            if ($productionSteps->isNotEmpty()) {
                                $stepUuid = $productionSteps->random()->uuid;
                            }
                        }

                        return [
                            'product_uuid' => $product->uuid,
                            'production_step_uuid' => $stepUuid,];
                    })
                    ->afterCreating(function (ProductionOrderItem $item) use ($productionSteps, $company, $user) {
                        if ($item->quantity_planned <= 0 || $productionSteps->isEmpty()) return;

                        $numLogs = rand(1, max(2, (int)ceil($item->quantity_planned / 10)));
                        $remainingQuantity = $item->quantity_planned;
                        $orderStartDate = $item->productionOrder->start_date;
                        $totalLoggedQuantity = 0;
                        for ($i = 0; $i < $numLogs && $remainingQuantity > 0; $i++) {
                            $step = $productionSteps->random();

                            $logQuantity = ($i === $numLogs - 1)
                                ? $remainingQuantity
                                : rand(1, max(1, (int)floor($remainingQuantity / ($numLogs - $i))));
                            $logQuantity = max(1, (int)floor($logQuantity));
                            $logQuantity = min($remainingQuantity, $logQuantity);

                            if ($logQuantity <= 0) continue;

                            ProductionLog::factory()
                                ->for($company)
                                ->for($user)
                                ->create([
                                    'production_order_item_uuid' => $item->uuid,
                                    'production_step_uuid' => $step->uuid,
                                    'quantity' => $logQuantity,
                                    'log_time' => fake()->dateTimeBetween($orderStartDate ?? '-1 week', 'now'),
                                ]);

                            $remainingQuantity -= $logQuantity;
                            $totalLoggedQuantity += $logQuantity;
                        }

                        if ($totalLoggedQuantity > 0) {
                            $item->update(['quantity_produced' => ceil(fake()->numberBetween(0, 100) / 100 * $totalLoggedQuantity)]);
                        }
                    }),
                'items')
            ->create();
        $this->command->info('Ordens de Produção, Itens e Logs criados.');
    }

}
