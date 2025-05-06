<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductionLog;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\ProductionStep; // Importar ProductionStep
use App\Models\User; // Importar User (para associar à ordem)
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductionOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Obter a empresa para a qual criar as ordens
        $company = Company::first();
        if (!$company) {
            $this->command->error('Nenhuma empresa encontrada. Crie uma empresa antes de rodar o ProductionOrderSeeder.');
            return;
        }
        $this->command->info("Usando a empresa: {$company->name} ({$company->uuid})");

        // 2. Obter produtos APENAS desta empresa
        // Usamos withoutGlobalScope para garantir que pegamos os produtos certos,
        // caso o TenantScope esteja ativo por algum motivo, mas filtramos manualmente.
        // Ou melhor, confiamos que o TenantScope NÃO está ativo e filtramos.
        $products = Product::where('company_id', $company->uuid)->get();
        if ($products->isEmpty()) {
            $this->command->warn("Nenhum produto encontrado para a empresa {$company->name}. Rode o ProductSeeder para esta empresa primeiro.");
            return;
        }

        // 3. Obter etapas de produção APENAS desta empresa
        $productionSteps = ProductionStep::where('company_id', $company->uuid)->get();
        if ($productionSteps->isEmpty()) {
            $this->command->warn("Nenhuma etapa de produção encontrada para a empresa {$company->name}. Rode o ProductionStepSeeder para esta empresa primeiro.");
            return;
        }

        // 4. Obter um usuário desta empresa para associar à ordem (opcional, mas bom ter)
        $user = User::where('company_id', $company->uuid)->first();
        if (!$user) {
            $this->command->warn("Nenhum usuário encontrado para a empresa {$company->name}. Criando um usuário padrão.");
            // Cria um usuário se não existir (ajuste a factory se necessário)
            $user = User::factory()->for($company)->create();
        }


        $this->command->info('Criando Ordens de Produção, Itens e Logs...');

        // 5. Criar as Ordens de Produção associadas à empresa e usuário
        ProductionOrder::factory(10)
            ->for($company) // <-- Associa a Ordem à Empresa
            ->for($user)    // <-- Associa a Ordem ao Usuário
            ->has(
                ProductionOrderItem::factory()
                    ->count(rand(1, 3))
                    ->for($company) // <-- Associa o Item à Empresa
                    ->state(function (array $attributes, ProductionOrder $order) use ($products, $productionSteps) { // Adiciona $productionSteps aqui
                        // Pega um produto aleatório DA EMPRESA CORRETA
                        $product = $products->whereNotIn('uuid', $order->items->pluck('product_uuid'))->random();

                        // --- INÍCIO DA LÓGICA PARA DEFINIR A ETAPA ---
                        $stepUuid = null;
                        // Tenta encontrar a primeira etapa associada a este produto
                        // Carrega as etapas do produto ordenadas por 'step_order'
                        $productSteps = $product->productionSteps()->orderBy('pivot_step_order')->get(); // Usa o nome padrão da coluna pivot

                        if ($productSteps->isNotEmpty()) {
                            // Pega o UUID da primeira etapa
                            $stepUuid = $productSteps->first()->uuid;
                        } else {
                            if ($productionSteps->isNotEmpty()) {
                                $stepUuid = $productionSteps->random()->uuid;
                                // $this->command->warn("Produto {$product->name} sem etapas vinculadas, usando etapa aleatória {$stepUuid} para o item.");
                            }
                        }
                        // --- FIM DA LÓGICA PARA DEFINIR A ETAPA ---

                        return [
                            'product_uuid' => $product->uuid,
                            'production_step_uuid' => $stepUuid, // <-- DEFINE A ETAPA NO ITEM
                        ];
                    })
                    ->afterCreating(function (ProductionOrderItem $item) use ($productionSteps, $company, $user) { // Passa $company e $user
                        if ($item->quantity_planned <= 0 || $productionSteps->isEmpty()) return;

                        $numLogs = rand(1, max(2, (int)ceil($item->quantity_planned / 10)));
                        $remainingQuantity = $item->quantity_planned;
                        $orderStartDate = $item->productionOrder->start_date;
                        $totalLoggedQuantity = 0; // <-- Variável para somar o logado

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

                        // --- ATUALIZA O ITEM APÓS CRIAR OS LOGS ---
                        if ($totalLoggedQuantity > 0) {
                            $item->update(['quantity_produced' => ceil(fake()->numberBetween(0,100) / 100 * $totalLoggedQuantity)]);
                        }
                    }),
                'items' // Nome da relação em ProductionOrder
            )
            ->create(); // Cria a ProductionOrder e seus itens/logs aninhados

        $this->command->info('Ordens de Produção, Itens e Logs criados.');
    }

}
