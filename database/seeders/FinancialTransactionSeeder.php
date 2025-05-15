<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\FinancialTransaction;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FinancialTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        $users = User::all(); // Pega todos os usuários
        $chartOfAccounts = ChartOfAccount::all(); // Pega todas as contas

        if ($users->isEmpty()) {
            $this->command->warn('Nenhum usuário encontrado. Crie usuários antes de popular as transações financeiras.');
            return;
        }

        if ($chartOfAccounts->isEmpty()) {
            $this->command->warn('Nenhum plano de contas encontrado. Crie o plano de contas antes de popular as transações.');
            return;
        }

        // Filtrar contas que podem receber lançamentos (geralmente as folhas da árvore ou tipos específicos)
        // Para este exemplo, vamos pegar contas de Receita e Despesa que não têm filhos (folhas)
        $transactionableAccounts = $chartOfAccounts->filter(function ($account) {
            return in_array($account->type, [ChartOfAccount::TYPE_REVENUE, ChartOfAccount::TYPE_EXPENSE]) && $account->childAccounts()->count() === 0;
        });

        if ($transactionableAccounts->isEmpty()) {
            $this->command->warn('Nenhuma conta de receita/despesa folha encontrada para lançamentos. Verifique o plano de contas.');
            return;
        }

        $this->command->info('Criando transações financeiras...');

        for ($i = 0; $i < 200; $i++) { // Criar 200 transações de exemplo
            $account = $transactionableAccounts->random();
            $user = $users->random(); // Pega um usuário aleatório

            $transactionType = $account->type === ChartOfAccount::TYPE_REVENUE ? FinancialTransaction::TYPE_INCOME : FinancialTransaction::TYPE_EXPENSE;

            // Se for uma conta de "DEDUÇÕES DA RECEITA BRUTA", o tipo da transação deve ser 'expense' (saída)
            // mas a conta em si é do tipo 'revenue' no plano de contas.
            if (Str::contains($account->name, 'DEDUÇÕES DA RECEITA BRUTA') || Str::contains($account->name, 'Impostos Sobre Vendas')) {
                $transactionType = FinancialTransaction::TYPE_EXPENSE;
            }


            FinancialTransaction::create([
                'uuid' => Str::uuid()->toString(),
                'company_id' => $account->company_id, // Pega o company_id da conta
                'chart_of_account_uuid' => $account->uuid,
                'user_id' => $user->uuid, // Associa a um usuário
                'description' => $faker->sentence(4),
                'amount' => $faker->randomFloat(2, 10, 5000),
                'type' => $transactionType,
                'transaction_date' => $faker->dateTimeBetween(Carbon::now()->subYear(), Carbon::now())->format('Y-m-d'),
                'notes' => $faker->optional(0.3)->paragraph, // 30% de chance de ter notas
                'payment_method' => $faker->optional(0.7)->randomElement(['Boleto', 'Cartão de Crédito', 'Transferência', 'Pix', 'Dinheiro']),
                'reference_document' => $faker->optional(0.5)->bothify('NF-#####'),
            ]);
        }
        $this->command->info('Transações financeiras criadas.');
    }
}
