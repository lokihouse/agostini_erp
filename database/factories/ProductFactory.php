<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Product; // Certifique-se que o namespace do modelo está correto
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class; // Garante que o modelo está correto

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(rand(2, 4), true); // Gera um nome com 2 a 4 palavras
        return [
            'company_id' => Company::factory(),
            'name' => Str::title($name), // Capitaliza as palavras do nome
            'sku' => $this->faker->unique()->ean8(), // Gera um código de barras EAN-8 único
            'description' => $this->faker->sentence(10), // Gera uma frase com 10 palavras
            'unit_of_measure' => $this->faker->randomElement(['unidade', 'peça', 'kg', 'litro', 'metro']), // Escolhe uma unidade aleatória
            // Adicione outros campos se necessário
            'standard_cost' => $this->faker->randomFloat(2, 10, 100),
            'sale_price' => $this->faker->randomFloat(2, 50, 500),
        ];
    }

    public function forCompany(Company $company): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $company->uuid,
        ]);
    }
}

