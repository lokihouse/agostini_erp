<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\ProductionStep; // Verifique o namespace
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductionStep>
 */
class ProductionStepFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductionStep::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Lista de etapas comuns para dar mais sentido
        $steps = ['Corte', 'Dobra', 'Usinagem', 'Solda', 'Montagem', 'Inspeção', 'Teste', 'Pintura', 'Acabamento', 'Embalagem'];

        return [
            'company_id' => Company::factory(),
            'name' => $this->faker->unique()->randomElement($steps), // Pega um nome único da lista
            'description' => $this->faker->sentence(5),
            'default_order' => $this->faker->optional(0.8)->numberBetween(1, 10), // 80% de chance de ter uma ordem padrão
        ];
    }

    public function forCompany(Company $company): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $company->uuid,
        ]);
    }
}

