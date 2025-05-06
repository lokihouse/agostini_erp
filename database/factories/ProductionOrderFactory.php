<?php

namespace Database\Factories;

use App\Models\ProductionOrder; // Verifique o namespace
use App\Models\User; // Precisamos de um usuário
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductionOrder>
 */
class ProductionOrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductionOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->optional(0.7)->dateTimeBetween('-1 month', 'now'); // 70% chance de ter iniciado
        $completionDate = null;
        $status = 'Pendente';

        if ($startDate) {
            $status = $this->faker->randomElement(['Em Andamento', 'Concluída']);
            if ($status === 'Concluída') {
                $completionDate = $this->faker->dateTimeBetween($startDate, 'now');
            }
        }

        return [
            // Gera um número de ordem único no formato OP-ANO-SEQUENCIAL
            'order_number' => 'OP-' . date('Y') . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'due_date' => $this->faker->dateTimeBetween('now', '+2 months')->format('Y-m-d'),
            'start_date' => $startDate,
            'completion_date' => $completionDate,
            'status' => $status,
            'notes' => $this->faker->optional()->sentence(), // Nota opcional
            // Pega um usuário aleatório que já exista ou cria um novo se não houver
            'user_uuid' => User::inRandomOrder()->first()?->uuid ?? User::factory(),
        ];
    }
}

