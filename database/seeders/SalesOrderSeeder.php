<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Company;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesVisit;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalesOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        $companies = Company::all();

        if ($companies->isEmpty()) {
            $this->command->warn('Nenhuma empresa encontrada. Crie empresas antes de popular os pedidos de venda.');
            return;
        }

        foreach ($companies as $company) {
            $clients = Client::where('company_id', $company->uuid)->get();
            $users = User::where('company_id', $company->uuid)->get();
            $products = Product::where('company_id', $company->uuid)->get();
            // Visitas concluídas da empresa que ainda não têm um pedido associado
            $availableVisits = SalesVisit::where('company_id', $company->uuid)
                ->where('status', SalesVisit::STATUS_COMPLETED)
                ->whereNull('sales_order_id')
                ->whereNull('report_reason_no_order') // Apenas visitas que deveriam ter gerado pedido
                ->get();

            if ($clients->isEmpty() || $users->isEmpty() || $products->isEmpty()) {
                $this->command->warn("Empresa {$company->name} não possui clientes, usuários ou produtos suficientes. Pulando pedidos para esta empresa.");
                continue;
            }

            $this->command->info("Criando pedidos de venda para a empresa: {$company->name}");

            for ($i = 0; $i < 30; $i++) { // Criar 30 pedidos por empresa
                $client = $clients->random();
                $user = $users->random();
                $salesVisit = null;

                // 50% de chance de associar a uma visita disponível
                if ($availableVisits->isNotEmpty() && $faker->boolean(50)) {
                    $salesVisit = $availableVisits->pop(); // Pega uma visita e remove da coleção para não usar de novo
                    if ($salesVisit) {
                        $client = $salesVisit->client; // Garante que o cliente do pedido é o mesmo da visita
                    }
                }

                $orderDate = $salesVisit ? $salesVisit->visited_at ?? Carbon::instance($faker->dateTimeBetween('-2 months', 'now')) : Carbon::instance($faker->dateTimeBetween('-2 months', 'now'));
                $deliveryDeadline = $faker->optional(0.8)->dateTimeBetween($orderDate->copy()->addDays(5), $orderDate->copy()->addDays(45));

                $status = $faker->randomElement([
                    SalesOrder::STATUS_DRAFT,
                    SalesOrder::STATUS_PENDING,
                    SalesOrder::STATUS_APPROVED,
                    SalesOrder::STATUS_PROCESSING,
                    SalesOrder::STATUS_SHIPPED,
                    SalesOrder::STATUS_DELIVERED,
                    SalesOrder::STATUS_CANCELLED,
                ]);

                $cancelledAt = null;
                $cancellationReason = null;
                $cancellationDetails = null;

                if ($status === SalesOrder::STATUS_CANCELLED) {
                    $cancelledAt = $faker->dateTimeBetween($orderDate, Carbon::now());
                    $cancellationReason = $faker->sentence;
                    $cancellationDetails = $faker->optional(0.5)->paragraph;
                }

                // A lógica de order_number e total_amount está no modelo SalesOrder
                $salesOrder = SalesOrder::create([
                    'uuid' => Str::uuid()->toString(),
                    'company_id' => $company->uuid,
                    'client_id' => $client->uuid,
                    'sales_visit_id' => $salesVisit?->uuid,
                    'user_id' => $user->uuid,
                    'order_date' => $orderDate,
                    'delivery_deadline' => $deliveryDeadline ? Carbon::parse($deliveryDeadline) : null,
                    'status' => $status,
                    'notes' => $faker->optional(0.6)->paragraph,
                    'cancellation_reason' => $cancellationReason,
                    'cancellation_details' => $cancellationDetails,
                    'cancelled_at' => $cancelledAt,
                    // 'total_amount' será calculado
                ]);

                if ($salesVisit && $salesOrder) {
                    $salesVisit->sales_order_id = $salesOrder->uuid;
                    $salesVisit->save();
                }

                // Adicionar itens ao pedido (exceto se for rascunho ou cancelado inicialmente)
                if ($salesOrder && !in_array($status, [SalesOrder::STATUS_DRAFT, SalesOrder::STATUS_CANCELLED])) {
                    $numberOfItems = $faker->numberBetween(1, 5);
                    $usedProductIds = []; // Para evitar adicionar o mesmo produto várias vezes no mesmo pedido

                    for ($j = 0; $j < $numberOfItems; $j++) {
                        $product = $products->whereNotIn('uuid', $usedProductIds)->random();
                        if (!$product) continue; // Caso todos os produtos já tenham sido usados
                        $usedProductIds[] = $product->uuid;

                        $quantity = $faker->numberBetween(1, 10);
                        $unitPrice = $product->sale_price;
                        // 30% de chance de ter um pequeno desconto
                        $discountAmount = $faker->boolean(30) ? $faker->randomFloat(2, 0, $unitPrice * 0.1) : 0;
                        // Garante que o desconto não seja maior que o preço mínimo, se houver
                        if ($product->minimum_sale_price !== null && ($unitPrice - $discountAmount) < $product->minimum_sale_price) {
                            $discountAmount = $unitPrice - $product->minimum_sale_price;
                            $discountAmount = max(0, $discountAmount); // Garante que não seja negativo
                        }


                        SalesOrderItem::create([
                            'uuid' => Str::uuid()->toString(),
                            'company_id' => $company->uuid,
                            'sales_order_id' => $salesOrder->uuid,
                            'product_id' => $product->uuid,
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'discount_amount' => $discountAmount,
                            // 'final_price' e 'total_price' são calculados no modelo SalesOrderItem
                            'notes' => $faker->optional(0.2)->sentence,
                        ]);
                    }
                    $salesOrder->updateTotalAmount(); // Recalcula e salva o total do pedido
                }
            }
        }
        $this->command->info('Pedidos de venda e seus itens criados.');
    }
}
