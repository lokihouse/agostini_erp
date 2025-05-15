<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\WorkShift;
use Illuminate\Database\Seeder;

class WorkShiftSeeder extends Seeder
{
    
    public function run(): void
    {
                if (Company::count() === 0) {
            $this->command->info('Nenhuma empresa encontrada, criando algumas empresas primeiro...');
            Company::factory(3)->create();             $this->command->info('Empresas criadas.');
        }

        $companies = Company::all();

        if ($companies->isEmpty()) {
            $this->command->error('Não foi possível criar ou encontrar empresas. WorkShiftSeeder não será executado.');
            return;
        }

        foreach ($companies as $company) {
                        WorkShift::factory(1)->state(['company_id' => $company->uuid])->weekly()->create();

                        WorkShift::factory(1)->state(['company_id' => $company->uuid])->cyclical()->create([
                'name' => 'Escala 12x36 Diurna ' . $company->name,
                'cycle_work_duration_hours' => 12,
                'cycle_off_duration_hours' => 36,
                'cycle_shift_starts_at' => '07:00:00',
                'cycle_shift_ends_at' => '19:00:00',
                'cycle_interval_starts_at' => '12:00:00',
                'cycle_interval_ends_at' => '13:00:00',
            ]);
            
        }
    }
}
