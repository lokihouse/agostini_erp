<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\SalesGoal;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SalesGoalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        $companies = Company::all();

        if ($companies->isEmpty()) {
            $this->command->warn('Nenhuma empresa encontrada. Crie empresas antes de popular as metas de vendas.');
            return;
        }

        foreach ($companies as $company) {
            // Busca usuários da empresa que são vendedores ou administradores
            // Ajuste as roles ('Vendedor', 'Administrador') conforme a sua configuração
            $salespeople = User::where('company_id', $company->uuid)
                ->whereHas('roles', function ($query) {
                    $query->whereIn('name', ['Vendedor', 'Administrador']);
                })
                ->get();

            if ($salespeople->isEmpty()) {
                $this->command->warn("Nenhum vendedor encontrado para a empresa: {$company->name}. Pulando metas para esta empresa.");
                continue;
            }

            $this->command->info("Criando metas de vendas para a empresa: {$company->name}");

            foreach ($salespeople as $salesperson) {
                $this->command->line(" -> Gerando metas para o vendedor: {$salesperson->name}");

                // Gerar metas para os últimos 6 meses e próximos 3 meses
                for ($m = -6; $m <= 3; $m++) {
                    $periodDate = Carbon::now()->addMonths($m)->startOfMonth();

                    // Verifica se já existe uma meta para este vendedor neste período
                    $existingGoal = SalesGoal::where('company_id', $company->uuid)
                        ->where('user_id', $salesperson->uuid)
                        ->where('period', $periodDate->toDateString())
                        ->first();

                    if ($existingGoal) {
                        $this->command->line("    * Meta para {$periodDate->format('M/Y')} já existe. Pulando.");
                        continue;
                    }

                    SalesGoal::create([
                        'uuid' => Str::uuid()->toString(),
                        'company_id' => $company->uuid,
                        'user_id' => $salesperson->uuid,
                        'period' => $periodDate, // O model SalesGoal já trata para ser o primeiro dia do mês
                        'goal_amount' => $faker->randomFloat(2, 5000, 50000), // Valor da meta entre 5k e 50k
                    ]);
                    $this->command->line("    * Meta para {$periodDate->format('M/Y')} criada: R$ " . number_format(SalesGoal::latest('created_at')->first()->goal_amount, 2, ',', '.'));
                }
            }
        }
        $this->command->info('Metas de vendas criadas.');
    }
}
