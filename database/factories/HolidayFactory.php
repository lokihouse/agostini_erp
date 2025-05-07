<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Holiday;
use Illuminate\Database\Eloquent\Factories\Factory;

class HolidayFactory extends Factory
{
    protected $model = Holiday::class;

    public function definition(): array
    {
        $types = ['national', 'state', 'municipal', 'optional_point'];
        $isCompanySpecific = $this->faker->boolean(70); // 70% chance de ser específico da empresa

        return [
            'company_id' => $isCompanySpecific && Company::count() > 0 ? Company::inRandomOrder()->first()->uuid : null,
            'name' => $this->faker->words(3, true),
            'date' => $this->faker->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'type' => $this->faker->randomElement($types),
            'is_recurrent' => $this->faker->boolean(80), // 80% chance de ser recorrente
            'notes' => $this->faker->optional()->sentence,
        ];
    }

    /**
     * Indicate that the holiday is global (national).
     */
    public function global(string $name, string $date, string $type = 'national', bool $recurrent = true): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => null,
            'name' => $name,
            'date' => $date,
            'type' => $type,
            'is_recurrent' => $recurrent,
        ]);
    }

    /**
     * Indicate that the holiday is specific to a company.
     */
    public function forCompany(Company $company, string $name, string $date, string $type = 'municipal', bool $recurrent = true): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $company->uuid,
            'name' => $name,
            'date' => $date,
            'type' => $type,
            'is_recurrent' => $recurrent,
        ]);
    }
}
