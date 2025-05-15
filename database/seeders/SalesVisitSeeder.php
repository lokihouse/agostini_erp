<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Company;
use App\Models\SalesVisit;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SalesVisitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        $companies = Company::all();

        if ($companies->isEmpty()) {
            $this->command->warn('Nenhuma empresa encontrada. Crie empresas antes de popular as visitas de venda.');
            return;
        }

        foreach ($companies as $company) {
            $clients = Client::where('company_id', $company->uuid)->get();
            $users = User::where('company_id', $company->uuid)->get(); // Vendedores da mesma empresa

            if ($clients->isEmpty()) {
                $this->command->warn("Nenhum cliente encontrado para a empresa: {$company->name}. Pulando visitas para esta empresa.");
                continue;
            }

            if ($users->isEmpty()) {
                $this->command->warn("Nenhum usuário (vendedor) encontrado para a empresa: {$company->name}. Pulando visitas para esta empresa.");
                continue;
            }

            $this->command->info("Criando visitas de venda para a empresa: {$company->name}");

            for ($i = 0; $i < 20; $i++) { // Criar 20 visitas por empresa
                $client = $clients->random();
                $scheduledByUser = $users->random();
                $assignedToUser = $users->random();

                $scheduledAt = Carbon::instance($faker->dateTimeBetween('-3 months', '+1 month'));
                $status = $faker->randomElement([
                    SalesVisit::STATUS_SCHEDULED,
                    SalesVisit::STATUS_IN_PROGRESS,
                    SalesVisit::STATUS_COMPLETED,
                    SalesVisit::STATUS_CANCELLED,
                    SalesVisit::STATUS_RESCHEDULED,
                ]);

                $visitedAt = null;
                $visitStartTime = null;
                $visitEndTime = null;
                $notes = $faker->optional(0.7)->paragraph;
                $cancellationReason = null;
                $cancellationDetails = null;
                $reportReasonNoOrder = null;
                $reportCorrectiveActions = null;

                if ($scheduledAt->isPast() && $status !== SalesVisit::STATUS_SCHEDULED && $status !== SalesVisit::STATUS_RESCHEDULED) {
                    if ($status === SalesVisit::STATUS_IN_PROGRESS) {
                        $visitStartTime = $scheduledAt->copy()->addMinutes($faker->numberBetween(0, 60));
                    } elseif ($status === SalesVisit::STATUS_COMPLETED) {
                        $visitStartTime = $scheduledAt->copy()->addMinutes($faker->numberBetween(0, 30));
                        $visitEndTime = $visitStartTime->copy()->addHours($faker->numberBetween(1, 3))->addMinutes($faker->numberBetween(0, 59));
                        $visitedAt = $visitEndTime;
                        if ($faker->boolean(30)) { // 30% chance de não ter pedido
                            $reportReasonNoOrder = $faker->sentence;
                            $reportCorrectiveActions = $faker->optional(0.5)->paragraph;
                        }
                    } elseif ($status === SalesVisit::STATUS_CANCELLED) {
                        $cancellationReason = $faker->sentence;
                        $cancellationDetails = $faker->optional(0.5)->paragraph;
                    }
                } else if ($scheduledAt->isFuture() && !in_array($status, [SalesVisit::STATUS_SCHEDULED, SalesVisit::STATUS_RESCHEDULED])) {
                    // Se agendada para o futuro, mas status é de algo que já ocorreu, força para 'scheduled'
                    $status = SalesVisit::STATUS_SCHEDULED;
                }


                SalesVisit::create([
                    'uuid' => Str::uuid()->toString(),
                    'company_id' => $company->uuid,
                    'client_id' => $client->uuid,
                    'scheduled_by_user_id' => $scheduledByUser->uuid,
                    'assigned_to_user_id' => $assignedToUser->uuid,
                    'scheduled_at' => $scheduledAt,
                    'visited_at' => $visitedAt,
                    'status' => $status,
                    'notes' => $notes,
                    'cancellation_reason' => $cancellationReason,
                    'cancellation_details' => $cancellationDetails,
                    // 'sales_order_id' será preenchido pelo SalesOrderSeeder se necessário
                    'visit_start_time' => $visitStartTime,
                    'visit_end_time' => $visitEndTime,
                    'report_reason_no_order' => $reportReasonNoOrder,
                    'report_corrective_actions' => $reportCorrectiveActions,
                ]);
            }
        }
        $this->command->info('Visitas de venda criadas.');
    }
}
