<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WorkSlot;
use App\Models\Company; // <-- Importar Company

class WorkSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Obter a empresa válida (mesma lógica dos outros seeders)
        $company = Company::first();

        // Verificar se uma empresa foi encontrada
        if (!$company) {
            $this->command->error('Nenhuma empresa encontrada. Crie uma empresa antes de rodar o WorkSlotSeeder.');
            return; // Interrompe o seeder
        }
        $this->command->info("Criando Work Slots para a empresa: {$company->name}");

        // 2. Criar Work Slots associados a essa empresa
        // Exemplo: Criar alguns slots padrão
        $slotsData = [
            ['name' => 'Bancada A', 'location' => 'Setor 1', 'is_active' => true],
            ['name' => 'Máquina CNC 01', 'location' => 'Setor 2', 'is_active' => true],
            ['name' => 'Estação de Montagem 1', 'location' => 'Setor 3', 'is_active' => true],
            ['name' => 'Estação de Teste', 'location' => 'Setor 4', 'is_active' => false], // Exemplo inativo
        ];

        foreach ($slotsData as $slotInfo) {
            WorkSlot::factory()
                ->for($company) // <-- Associar à empresa!
                // OU ->state(['company_id' => $company->uuid])
                ->create($slotInfo); // Passa os dados específicos do slot
        }

        // Ou usar apenas a factory para gerar dados aleatórios:
        // WorkSlot::factory()
        //     ->count(5) // Quantidade desejada
        //     ->for($company) // <-- Associar à empresa!
        //     ->create();

        $this->command->info('Work Slots criados.');
    }
}
