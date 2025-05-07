<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\WorkShift;
use App\Models\WorkShiftDay;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class WorkShiftFactory extends Factory
{
    protected $model = WorkShift::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['weekly', 'cyclical']);
        $company = Company::query()->inRandomOrder()->first() ?? Company::factory()->create();

        $attributes = [
            'company_id' => $company->uuid,
            'name' => $this->faker->words(3, true) . ' Shift',
            'type' => $type,
            'notes' => $this->faker->optional()->sentence,
        ];

        if ($type === 'cyclical') {
            $workHours = $this->faker->randomElement([8, 12]);
            $offHours = $workHours === 12 ? 36 : $this->faker->numberBetween(12, 24); // Example: 12x36 or 8x(12 to 24)

            $shiftStartHour = $this->faker->numberBetween(6, 22);
            $shiftStartTime = Carbon::createFromTime($shiftStartHour, 0, 0);
            $shiftEndTime = $shiftStartTime->copy()->addHours($workHours);

            // Interval for cyclical (example: 1 hour after 4 hours of work if workHours > 6)
            $intervalStartTime = null;
            $intervalEndTime = null;
            if ($workHours > 6) {
                $intervalStartTime = $shiftStartTime->copy()->addHours(4)->format('H:i:s');
                $intervalEndTime = $shiftStartTime->copy()->addHours(5)->format('H:i:s');
            } elseif ($workHours >= 4) {
                $intervalStartTime = $shiftStartTime->copy()->addHours(2)->format('H:i:s');
                $intervalEndTime = $shiftStartTime->copy()->addHours(2)->addMinutes(15)->format('H:i:s');
            }


            $attributes = array_merge($attributes, [
                'cycle_work_duration_hours' => $workHours,
                'cycle_off_duration_hours' => $offHours,
                'cycle_shift_starts_at' => $shiftStartTime->format('H:i:s'),
                'cycle_shift_ends_at' => $shiftEndTime->format('H:i:s'),
                'cycle_interval_starts_at' => $intervalStartTime,
                'cycle_interval_ends_at' => $intervalEndTime,
            ]);
        }

        return $attributes;
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure(): static
    {
        return $this->afterCreating(function (WorkShift $workShift) {
            if ($workShift->type === 'weekly') {
                $daysOfWeek = range(1, 7);
                shuffle($daysOfWeek); // Randomize days to have at least one off day

                $offDayAssigned = false;
                $workDaysCount = 0;

                foreach ($daysOfWeek as $i => $day) {
                    // Ensure at least one off day, and not more than 6 work days
                    $isOffDay = false;
                    if (!$offDayAssigned && $i >= count($daysOfWeek) - 2) { // Try to make one of the last two an off day if none yet
                        $isOffDay = true;
                        $offDayAssigned = true;
                    } elseif ($workDaysCount >= 6) {
                        $isOffDay = true;
                        $offDayAssigned = true; // Ensure offDayAssigned is true if we force an off day
                    } else {
                        $isOffDay = $this->faker->boolean(20); // 20% chance of being an off day
                        if ($isOffDay) {
                            $offDayAssigned = true;
                        }
                    }

                    if (!$isOffDay) {
                        $workDaysCount++;
                    }


                    $dayAttributes = [
                        'work_shift_uuid' => $workShift->uuid,
                        'day_of_week' => $day,
                        'is_off_day' => $isOffDay,
                        'starts_at' => null,
                        'ends_at' => null,
                        'interval_starts_at' => null,
                        'interval_ends_at' => null,
                    ];

                    if (!$isOffDay) {
                        $startHour = $this->faker->numberBetween(7, 10); // e.g., 08:00
                        $workDurationHours = $this->faker->numberBetween(4, 8);

                        $startsAt = Carbon::createFromTime($startHour, 0, 0);
                        $endsAt = $startsAt->copy()->addHours($workDurationHours);

                        $dayAttributes['starts_at'] = $startsAt->format('H:i:s');
                        $dayAttributes['ends_at'] = $endsAt->format('H:i:s');

                        // Gross duration for interval logic
                        $grossMinutes = $workDurationHours * 60;

                        if ($grossMinutes > (6 * 60)) { // More than 6 hours
                            $dayAttributes['interval_starts_at'] = $startsAt->copy()->addHours(4)->format('H:i:s');
                            $dayAttributes['interval_ends_at'] = $startsAt->copy()->addHours(5)->format('H:i:s'); // 1 hour interval
                        } elseif ($grossMinutes >= (4 * 60)) { // 4 to 6 hours
                            $dayAttributes['interval_starts_at'] = $startsAt->copy()->addHours(2)->format('H:i:s');
                            $dayAttributes['interval_ends_at'] = $startsAt->copy()->addHours(2)->addMinutes(15)->format('H:i:s'); // 15 min interval
                        }
                    }
                    WorkShiftDay::factory()->create($dayAttributes);
                }

                // If after iterating all days, no off day was assigned (e.g. all 7 were working days initially)
                // and we have 7 defined days, force one to be an off day.
                if (count($daysOfWeek) === 7 && !$offDayAssigned) {
                    $randomWorkDay = $workShift->workShiftDays()->where('is_off_day', false)->inRandomOrder()->first();
                    if ($randomWorkDay) {
                        $randomWorkDay->update(['is_off_day' => true, 'starts_at' => null, 'ends_at' => null, 'interval_starts_at' => null, 'interval_ends_at' => null]);
                    }
                }
            }
        });
    }

    /**
     * Indicate that the work shift is of type weekly.
     */
    public function weekly(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'weekly',
            ];
        });
    }

    /**
     * Indicate that the work shift is of type cyclical.
     */
    public function cyclical(): Factory
    {
        return $this->state(function (array $attributes) {
            $workHours = $this->faker->randomElement([8, 12]);
            $offHours = $workHours === 12 ? 36 : $this->faker->numberBetween(12, 24);

            $shiftStartHour = $this->faker->numberBetween(6, 22);
            $shiftStartTime = Carbon::createFromTime($shiftStartHour, 0, 0);
            $shiftEndTime = $shiftStartTime->copy()->addHours($workHours);

            $intervalStartTime = null;
            $intervalEndTime = null;
            if ($workHours > 6) {
                $intervalStartTime = $shiftStartTime->copy()->addHours(4)->format('H:i:s');
                $intervalEndTime = $shiftStartTime->copy()->addHours(5)->format('H:i:s');
            } elseif ($workHours >= 4) {
                $intervalStartTime = $shiftStartTime->copy()->addHours(2)->format('H:i:s');
                $intervalEndTime = $shiftStartTime->copy()->addHours(2)->addMinutes(15)->format('H:i:s');
            }

            return [
                'type' => 'cyclical',
                'cycle_work_duration_hours' => $workHours,
                'cycle_off_duration_hours' => $offHours,
                'cycle_shift_starts_at' => $shiftStartTime->format('H:i:s'),
                'cycle_shift_ends_at' => $shiftEndTime->format('H:i:s'),
                'cycle_interval_starts_at' => $intervalStartTime,
                'cycle_interval_ends_at' => $intervalEndTime,
            ];
        });
    }
}
