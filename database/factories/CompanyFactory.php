<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * O nome do model correspondente da factory.
     *
     * @var string
     */
    protected $model = Company::class;

    /**
     * Define o estado padrão do modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cnpj = $this->faker->unique()->numerify('##############'); // 14 dígitos

        return [
            'name' => $this->faker->company() . ' ' . $this->faker->companySuffix(),
            'socialName' => $this->faker->company() . ' Comércio e Indústria LTDA',
            'taxNumber' => $cnpj,
            'address' => $this->faker->streetAddress() . ', ' . $this->faker->buildingNumber() . ' - ' . $this->faker->city(),
            'telephone' => $this->faker->numerify('(##) #####-####'),
        ];
    }

    /**
     * Indica que a empresa está logicamente excluída.
     * (Útil se você estiver usando SoftDeletes)
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}
