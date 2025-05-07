<?php

namespace Database\Factories;

use App\Models\WorkShift;
use App\Models\WorkShiftDay;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class WorkShiftDayFactory extends Factory
{
    protected $model = WorkShiftDay::class;

    public function definition(): array
    {
        // Os atributos principais serão definidos pelo WorkShiftFactory
        // no hook afterCreating. Este factory pode ter defaults mínimos.
        $isOffDay = $this->faker->boolean(20); // 20% chance of being an off day by default

        $attributes = [
            // 'work_shift_uuid' => WorkShift::factory(), // Normalmente definido pelo chamador
            'day_of_week' => $this->faker->numberBetween(1, 7),
            'is_off_day' => $isOffDay,
            'starts_at' => null,
            'ends_at' => null,
            'interval_starts_at' => null,
            'interval_ends_at' => null,
        ];

        if (!$isOffDay) {
            $startHour = $this->faker->numberBetween(7, 10);
            $workDurationHours = $this->faker->numberBetween(4, 8);

            $startsAt = Carbon::createFromTime($startHour, 0, 0);
            $endsAt = $startsAt->copy()->addHours($workDurationHours);

            $attributes['starts_at'] = $startsAt->format('H:i:s');
            $attributes['ends_at'] = $endsAt->format('H:i:s');

            $grossMinutes = $workDurationHours * 60;

            if ($grossMinutes > (6 * 60)) {
                $attributes['interval_starts_at'] = $startsAt->copy()->addHours(4)->format('H:i:s');
                $attributes['interval_ends_at'] = $startsAt->copy()->addHours(5)->format('H:i:s');
            } elseif ($grossMinutes >= (4 * 60)) {
                $attributes['interval_starts_at'] = $startsAt->copy()->addHours(2)->format('H:i:s');
                $attributes['interval_ends_at'] = $startsAt->copy()->addHours(2)->addMinutes(15)->format('H:i:s');
            }
        }

        return $attributes;
    }
}
