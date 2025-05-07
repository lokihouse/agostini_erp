<?php

namespace Database\Factories;

use App\Models\Company; // Importar Company
use App\Models\WorkShift;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $company = Company::inRandomOrder()->first() ?? Company::factory()->create();
        $workShift = WorkShift::where('company_id', $company->uuid)->inRandomOrder()->first();

        return [
            'company_id' => $company->uuid,
            'work_shift_id' => $workShift?->uuid, // Atribui se encontrar uma jornada
            'name' => $this->faker->name(),
            'username' => $this->faker->unique()->userName(),
            'password' => static::$password ??= Hash::make('password'),
            'is_active' => $this->faker->boolean(90),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
