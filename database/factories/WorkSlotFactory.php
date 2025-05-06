<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Company; // <-- Importar Company

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkSlot>
 */
class WorkSlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Define um padrão para company_id
            'company_id' => Company::factory(), // <-- Cria uma Company se não for passada

            'name' => fake()->unique()->words(2, true) . ' ' . fake()->randomDigitNotNull(),
            'description' => fake()->optional()->sentence(),
            'location' => fake()->optional()->word() . ' ' . fake()->buildingNumber(),
            'is_active' => fake()->boolean(80), // 80% chance de ser ativo
        ];
    }

    // Opcional: Método para facilitar a definição da empresa
    public function forCompany(Company $company): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $company->uuid,
        ]);
    }
}
