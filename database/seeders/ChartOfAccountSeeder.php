<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * Define a estrutura base do plano de contas.
     * O código será gerado dinamicamente.
     */
    private array $baseChartStructure = [
        '1' => ['name' => 'ATIVO', 'type' => ChartOfAccount::TYPE_ASSET, 'children' => [
            '1.1' => ['name' => 'ATIVO CIRCULANTE', 'type' => ChartOfAccount::TYPE_ASSET, 'children' => [
                '1.1.1' => ['name' => 'Caixa e Equivalentes de Caixa', 'type' => ChartOfAccount::TYPE_ASSET],
                '1.1.2' => ['name' => 'Contas a Receber', 'type' => ChartOfAccount::TYPE_ASSET],
                '1.1.3' => ['name' => 'Estoques', 'type' => ChartOfAccount::TYPE_ASSET],
            ]],
            '1.2' => ['name' => 'ATIVO NÃO CIRCULANTE', 'type' => ChartOfAccount::TYPE_ASSET, 'children' => [
                '1.2.1' => ['name' => 'Imobilizado', 'type' => ChartOfAccount::TYPE_ASSET],
                '1.2.2' => ['name' => 'Intangível', 'type' => ChartOfAccount::TYPE_ASSET],
            ]],
        ]],
        '2' => ['name' => 'PASSIVO', 'type' => ChartOfAccount::TYPE_LIABILITY, 'children' => [
            '2.1' => ['name' => 'PASSIVO CIRCULANTE', 'type' => ChartOfAccount::TYPE_LIABILITY, 'children' => [
                '2.1.1' => ['name' => 'Fornecedores', 'type' => ChartOfAccount::TYPE_LIABILITY],
                '2.1.2' => ['name' => 'Empréstimos e Financiamentos', 'type' => ChartOfAccount::TYPE_LIABILITY],
                '2.1.3' => ['name' => 'Obrigações Sociais e Trabalhistas', 'type' => ChartOfAccount::TYPE_LIABILITY],
            ]],
            '2.2' => ['name' => 'PASSIVO NÃO CIRCULANTE', 'type' => ChartOfAccount::TYPE_LIABILITY, 'children' => [
                '2.2.1' => ['name' => 'Empréstimos e Financiamentos (Longo Prazo)', 'type' => ChartOfAccount::TYPE_LIABILITY],
            ]],
        ]],
        '3' => ['name' => 'PATRIMÔNIO LÍQUIDO', 'type' => ChartOfAccount::TYPE_EQUITY, 'children' => [
            '3.1' => ['name' => 'Capital Social', 'type' => ChartOfAccount::TYPE_EQUITY],
            '3.2' => ['name' => 'Reservas de Lucro', 'type' => ChartOfAccount::TYPE_EQUITY],
            '3.3' => ['name' => 'Lucros ou Prejuízos Acumulados', 'type' => ChartOfAccount::TYPE_EQUITY],
        ]],
        '4' => ['name' => 'RECEITAS', 'type' => ChartOfAccount::TYPE_REVENUE, 'children' => [
            '4.1' => ['name' => 'RECEITA OPERACIONAL BRUTA', 'type' => ChartOfAccount::TYPE_REVENUE, 'children' => [
                '4.1.1' => ['name' => 'Venda de Produtos', 'type' => ChartOfAccount::TYPE_REVENUE],
                '4.1.2' => ['name' => 'Prestação de Serviços', 'type' => ChartOfAccount::TYPE_REVENUE],
            ]],
            '4.2' => ['name' => 'DEDUÇÕES DA RECEITA BRUTA', 'type' => ChartOfAccount::TYPE_REVENUE, 'children' => [ // Considerado "redutor" de receita
                '4.2.1' => ['name' => 'Impostos Sobre Vendas e Serviços', 'type' => ChartOfAccount::TYPE_REVENUE],
            ]],
            '4.3' => ['name' => 'OUTRAS RECEITAS OPERACIONAIS', 'type' => ChartOfAccount::TYPE_REVENUE],
        ]],
        '5' => ['name' => 'DESPESAS', 'type' => ChartOfAccount::TYPE_EXPENSE, 'children' => [
            '5.1' => ['name' => 'CUSTOS DOS PRODUTOS VENDIDOS / SERVIÇOS PRESTADOS', 'type' => ChartOfAccount::TYPE_EXPENSE, 'children' => [
                '5.1.1' => ['name' => 'Custo da Mercadoria Vendida (CMV)', 'type' => ChartOfAccount::TYPE_EXPENSE],
                '5.1.2' => ['name' => 'Custo do Serviço Prestado (CSP)', 'type' => ChartOfAccount::TYPE_EXPENSE],
            ]],
            '5.2' => ['name' => 'DESPESAS OPERACIONAIS', 'type' => ChartOfAccount::TYPE_EXPENSE, 'children' => [
                '5.2.1' => ['name' => 'Despesas com Vendas', 'type' => ChartOfAccount::TYPE_EXPENSE],
                '5.2.2' => ['name' => 'Despesas Administrativas', 'type' => ChartOfAccount::TYPE_EXPENSE, 'children' => [
                    '5.2.2.1' => ['name' => 'Aluguel', 'type' => ChartOfAccount::TYPE_EXPENSE],
                    '5.2.2.2' => ['name' => 'Salários e Encargos (ADM)', 'type' => ChartOfAccount::TYPE_EXPENSE],
                    '5.2.2.3' => ['name' => 'Energia Elétrica (ADM)', 'type' => ChartOfAccount::TYPE_EXPENSE],
                    '5.2.2.4' => ['name' => 'Material de Escritório', 'type' => ChartOfAccount::TYPE_EXPENSE],
                ]],
            ]],
            '5.3' => ['name' => 'DESPESAS FINANCEIRAS', 'type' => ChartOfAccount::TYPE_EXPENSE],
        ]],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();

        if ($companies->isEmpty()) {
            $this->command->warn('Nenhuma empresa encontrada. Crie empresas antes de popular o plano de contas.');
            // Opcionalmente, crie uma empresa padrão aqui se desejar
            // Company::factory()->create(['name' => 'Empresa Padrão Seeder']);
            // $companies = Company::all();
            // if ($companies->isEmpty()) return;
            return;
        }

        foreach ($companies as $company) {
            $this->command->info("Criando plano de contas para a empresa: {$company->name}");
            $this->createAccountsForCompany($company, $this->baseChartStructure);
        }
    }

    private function createAccountsForCompany(Company $company, array $structure, ?string $parentUuid = null, string $parentCode = ''): void
    {
        foreach ($structure as $codeSegment => $accountData) {
            $currentFullCode = $parentCode ? $parentCode . '.' . $codeSegment : $codeSegment;

            // Verifica se já existe uma conta com este código para esta empresa
            $existingAccount = ChartOfAccount::where('company_id', $company->uuid)
                ->where('code', $currentFullCode)
                ->first();
            if ($existingAccount) {
                $this->command->line("Conta '{$currentFullCode} - {$accountData['name']}' já existe para {$company->name}. Pulando.");
                $createdAccountUuid = $existingAccount->uuid;
            } else {
                $account = ChartOfAccount::create([
                    'uuid' => Str::uuid()->toString(),
                    'company_id' => $company->uuid,
                    'code' => $currentFullCode,
                    'name' => $accountData['name'],
                    'type' => $accountData['type'],
                    'parent_uuid' => $parentUuid,
                ]);
                $createdAccountUuid = $account->uuid;
                $this->command->line("Criada conta: {$currentFullCode} - {$accountData['name']} para {$company->name}");
            }


            if (!empty($accountData['children'])) {
                // Extrai o último segmento do código atual para passar como base para os filhos
                $childCodePrefix = last(explode('.', $currentFullCode));
                $this->createAccountsForCompany($company, $accountData['children'], $createdAccountUuid, $currentFullCode);
            }
        }
    }
}
