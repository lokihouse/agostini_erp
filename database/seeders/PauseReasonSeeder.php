<?php

namespace Database\Seeders;

use App\Models\Company; // Importe o modelo Company se for criar motivos específicos de empresa
use App\Models\PauseReason;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str; // Para gerar UUIDs

class PauseReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Motivos de Pausa Globais (company_id = null)
        $globalReasons = [
            // Tempos Produtivos (contabilizam na produção)
            ['name' => 'Ajuste de Máquina (Rápido)', 'type' => PauseReason::TYPE_PRODUCTIVE_TIME, 'is_active' => true, 'notes' => 'Pequenos ajustes que não interrompem significativamente o fluxo.'],
            ['name' => 'Limpeza de Equipamento (Programada)', 'type' => PauseReason::TYPE_PRODUCTIVE_TIME, 'is_active' => true, 'notes' => 'Limpeza essencial para a continuidade da produção.'],
            ['name' => 'Setup/Troca de Ferramenta', 'type' => PauseReason::TYPE_PRODUCTIVE_TIME, 'is_active' => true],

            // Tempos Mortos (Não Produtivos)
            ['name' => 'Falta de Material', 'type' => PauseReason::TYPE_DEAD_TIME, 'is_active' => true],
            ['name' => 'Manutenção Corretiva Inesperada', 'type' => PauseReason::TYPE_DEAD_TIME, 'is_active' => true],
            ['name' => 'Problema de Qualidade (Análise)', 'type' => PauseReason::TYPE_DEAD_TIME, 'is_active' => true],
            ['name' => 'Aguardando Instrução/Liberação', 'type' => PauseReason::TYPE_DEAD_TIME, 'is_active' => true],
            ['name' => 'Reunião Não Planejada', 'type' => PauseReason::TYPE_DEAD_TIME, 'is_active' => true],

            // Pausas Obrigatórias
            ['name' => 'Intervalo para Refeição', 'type' => PauseReason::TYPE_MANDATORY_BREAK, 'is_active' => true],
            ['name' => 'Pausa para Café (Definida)', 'type' => PauseReason::TYPE_MANDATORY_BREAK, 'is_active' => true],
            ['name' => 'Ginástica Laboral', 'type' => PauseReason::TYPE_MANDATORY_BREAK, 'is_active' => true],
        ];

        foreach ($globalReasons as $reason) {
            PauseReason::firstOrCreate(
                ['name' => $reason['name'], 'company_id' => null], // Garante unicidade para globais
                [
                    'uuid' => (string) Str::uuid(), // Gera UUID se estiver criando
                    'type' => $reason['type'],
                    'is_active' => $reason['is_active'] ?? true,
                    'notes' => $reason['notes'] ?? null,
                ]
            );
        }

        $this->command->info('PauseReasonSeeder executado com sucesso!');
    }
}
