<?php

namespace Database\Seeders;

use App\Models\TimeClockEntry;
use App\Models\User;
use Illuminate\Database\Seeder;

class TimeClockEntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('Nenhum usuário encontrado. Crie usuários antes de popular as batidas de ponto.');
            return;
        }

        foreach ($users as $user) {
            // Exemplo: Criar batidas com status 'normal' por padrão no seeder
            TimeClockEntry::factory(10)->create([
                'user_id' => $user->uuid,
                'company_id' => $user->company_id,
                'status' => TimeClockEntry::STATUS_NORMAL // Definindo um status padrão
            ]);
        }

        $this->command->info('Batidas de ponto de exemplo criadas.');
    }
}
