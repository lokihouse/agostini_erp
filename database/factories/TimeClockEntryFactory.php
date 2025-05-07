<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\TimeClockEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeClockEntryFactory extends Factory
{
    protected $model = TimeClockEntry::class;

    public function definition(): array
    {
        $user = User::inRandomOrder()->first() ?? User::factory()->create();
        $types = array_keys(TimeClockEntry::getEntryTypeOptions());
        $statuses = array_keys(TimeClockEntry::getStatusOptions()); // Obter os status

        return [
            'user_id' => $user->uuid,
            'company_id' => $user->company_id ?? (Company::inRandomOrder()->first()->uuid ?? Company::factory()->create()->uuid),
            'recorded_at' => $this->faker->dateTimeThisMonth(),
            'type' => $this->faker->randomElement($types),
            'status' => $this->faker->randomElement($statuses), // Adicionado
            'latitude' => $this->faker->optional()->latitude(),
            'longitude' => $this->faker->optional()->longitude(),
            'ip_address' => $this->faker->optional()->ipv4(),
            'user_agent' => $this->faker->optional()->userAgent(),
            'notes' => $this->faker->optional()->sentence(),
            'approved_by' => null,
            'approved_at' => null,
        ];
    }
}
