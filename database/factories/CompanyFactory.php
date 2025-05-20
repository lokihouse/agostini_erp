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
            'name' => $this->faker->company,
            'socialName' => $this->faker->company . ' ' . $this->faker->companySuffix,
            'taxNumber' => $this->faker->unique()->numerify('##############'), // 14 digits for CNPJ
            'address_zip_code' => $this->faker->postcode,
            'address_street' => $this->faker->streetName,
            'address_number' => $this->faker->buildingNumber,
            'address_complement' => $this->faker->optional()->secondaryAddress,
            'address_district' => $this->faker->citySuffix . ' ' . $this->faker->streetSuffix, // Exemplo para bairro
            'address_city' => $this->faker->city,
            'address_state' => $this->faker->stateAbbr,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'telephone' => $this->faker->numerify('###########'),
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
