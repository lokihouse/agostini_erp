<?php

namespace Database\Factories;

use App\Models\ProductionOrderItem; // Verifique o namespace
use App\Models\ProductionOrder;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductionOrderItem>
 */
class ProductionOrderItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductionOrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $planned = $this->faker->numberBetween(10, 500);
        // A quantidade produzida será atualizada pelos logs, então começamos com 0
        $produced = 0;

        return [
            // As chaves estrangeiras geralmente são passadas ao chamar a factory
            'production_order_uuid' => ProductionOrder::factory(), // Cria uma ordem se não for passada
            'product_uuid' => Product::factory(), // Cria um produto se não for passado
            'quantity_planned' => $planned,
            'quantity_produced' => $produced, // Começa com 0
            'notes' => $this->faker->optional(0.2)->sentence(), // 20% chance de ter nota
        ];
    }
}

