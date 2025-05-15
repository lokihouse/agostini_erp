<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WorkSlot;
use App\Models\Company; 
class WorkSlotSeeder extends Seeder
{
    
    public function run(): void
    {
                $company = Company::first();

                if (!$company) {
            $this->command->error('Nenhuma empresa encontrada. Crie uma empresa antes de rodar o WorkSlotSeeder.');
            return;         }
        $this->command->info("Criando Work Slots para a empresa: {$company->name}");

                        $slotsData = [
            ['name' => 'Bancada A', 'location' => 'Setor 1', 'is_active' => true],
            ['name' => 'Máquina CNC 01', 'location' => 'Setor 2', 'is_active' => true],
            ['name' => 'Estação de Montagem 1', 'location' => 'Setor 3', 'is_active' => true],
            ['name' => 'Estação de Teste', 'location' => 'Setor 4', 'is_active' => false],         ];

        foreach ($slotsData as $slotInfo) {
            WorkSlot::factory()
                ->for($company)                                 ->create($slotInfo);         }

                                        
        $this->command->info('Work Slots criados.');
    }
}
