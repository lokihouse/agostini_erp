<?php

namespace Database\Factories;

use App\Models\ProductionLog; // Verifique o namespace
use App\Models\ProductionOrderItem;
use App\Models\ProductionStep;
use App\Models\WorkSlot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductionLog>
 */
class ProductionLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductionLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Pega um item de ordem aleatório existente ou cria um novo
        $orderItem = ProductionOrderItem::inRandomOrder()->first() ?? ProductionOrderItem::factory()->create();

        // Tenta pegar uma etapa associada ao produto do item da ordem
        $step = $orderItem->product->productionSteps()->inRandomOrder()->first() ?? ProductionStep::factory()->create();

        // Tenta pegar um slot associado à etapa
        $workSlot = $step->workSlots()->inRandomOrder()->first(); // Pode ser nulo

        // Garante que a quantidade registrada não exceda o que falta no item
        $maxQuantity = $orderItem->quantity_planned - $orderItem->quantity_produced;
        // Gera uma quantidade realista para um log (não tudo de uma vez)
        $quantity = $this->faker->numberBetween(1, max(1, min(50, (int)floor($maxQuantity * 0.3)))); // Produz até 30% do restante, max 50
        $quantity = max(0, $quantity); // Garante que não seja negativo se maxQuantity for 0

        // Define um tempo de log realista (depois do início da ordem, se houver)
        $logTime = $this->faker->dateTimeBetween(
            $orderItem->productionOrder->start_date ?? '-1 week', // Se a ordem não iniciou, pega da última semana
            'now'
        );

        return [
            'production_order_item_uuid' => $orderItem->uuid,
            'production_step_uuid' => $step->uuid,
            'work_slot_uuid' => $workSlot?->uuid, // Usa o UUID do slot se ele existir
            'user_uuid' => User::inRandomOrder()->first()?->uuid ?? User::factory(),
            'quantity' => $quantity,
            'log_time' => $logTime,
            'notes' => $this->faker->optional(0.1)->sentence(), // 10% chance de nota
        ];
    }
}

