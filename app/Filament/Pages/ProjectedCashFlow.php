<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\ChartOfAccount;
use App\Models\CashFlow;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Concerns\InteractsWithForms;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CashFlowExport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Htmlable;

class ProjectedCashFlow extends Page
{
    use InteractsWithForms;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Fluxo de Caixa Projetado';
    protected static ?string $navigationGroup = 'Financeiro';
    protected static ?int $navigationSort = 60;
    protected static string $view = 'filament.pages.projected-cash-flow';

    // Config
    public $monthsCount = 12; // alteração fácil para 6/12/24 meses
    public $monthHeaders = []; // Carbon objects
    public $monthStrings = []; // strings "YYYY-MM"
    public $accounts; // root accounts collection with children
    public $cashFlowsMap = []; // ['account_uuid']['YYYY-MM'] => amount (float)
    public $accountOptions = []; // uuid => "code - name"
    public $metaAccountUuid = null;
    public $investmentAccountUuid = null;
    public ?CashFlow $record = null;

    public function mount(Request $request): void
    {
        // 1) meses
        $currentYear = Carbon::now()->year;

        // gera de janeiro até dezembro do ano atual
        $this->monthHeaders = collect(range(1, 12))
            ->map(fn ($m) => Carbon::create($currentYear, $m, 1))
            ->toArray();

        $this->monthStrings = array_map(fn($c) => $c->format('Y-m'), $this->monthHeaders);


        // 2) contas raiz com filhos, apenas do tipo despesa
        $this->accounts = ChartOfAccount::whereNull('parent_uuid')
        ->where('company_id', Auth::user()->company_id) // 🔑 filtro pela empresa
        ->with(['childAccounts' => fn($q) => 
            $q->where('company_id', Auth::user()->company_id)
            ->with('childAccounts.childAccounts')
        ])
        ->orderBy('code')
        ->get();

        $allAccounts = ChartOfAccount::where('company_id', Auth::user()->company_id)
        ->orderBy('code')
        ->get();

        $this->accountOptions = $allAccounts->mapWithKeys(fn($a) => [$a->uuid => "{$a->code} - {$a->name}"])->toArray();

        // 3) opções planilhas (selects) - todas as contas, não só despesas
        $allAccounts = ChartOfAccount::orderBy('code')->get();
        $this->accountOptions = $allAccounts->mapWithKeys(fn($a) => [$a->uuid => "{$a->code} - {$a->name}"])->toArray();

        // 4) preload cash flows para todos accounts e meses (evita N+1)
        $allAccountUuids = $this->collectAllAccountUuids($this->accounts);
        $cashFlows = CashFlow::whereIn('chart_of_account_id', $allAccountUuids)
            ->whereIn('month', $this->monthStrings)
            ->get();
        
        $this->loadCashFlowsMap();
    }

    protected function collectAllAccountUuids($accounts): array
    {
        $uuids = [];
        foreach ($accounts as $acc) {
            $uuids[] = $acc->uuid;

            if ($acc->relationLoaded('childAccounts') && $acc->childAccounts->isNotEmpty()) {
                $uuids = array_merge($uuids, $this->collectAllAccountUuids($acc->childAccounts));
            } else {
                $children = ChartOfAccount::where('parent_uuid', $acc->uuid)
                    ->get();
                if ($children->isNotEmpty()) {
                    $uuids = array_merge($uuids, $this->collectAllAccountUuids($children));
                }
            }
        }
        return array_values(array_unique($uuids));
    }

    public function getCellValue(string $accountUuid, string $month): ?float
    {
        return $this->cashFlowsMap[$accountUuid][$month] ?? null;
    }

   public function downloadReport()
{
    $year = now()->year;
    $export = new \App\Exports\CashFlowExport($year, Auth::user()->company_id);
    return $export->download();
}

public function downloadPreviousYearReport()
{
    $year = now()->year - 1;
    $export = new \App\Exports\CashFlowExport($year, Auth::user()->company_id);
    return $export->download();
}


    public function updateCell(string $accountUuid, string $month, $rawValue): void
    {
        $value = $this->normalizeNumber($rawValue);

        if (!is_numeric($value)) {
            Notification::make()
                ->title('Valor inválido')
                ->danger()
                ->icon('heroicon-o-x-circle')
                ->send();
            return;
        }

        $companyId = Auth::user()->company_id ?? null;
        if (!$companyId) {
            Notification::make()
                ->title('Empresa não identificada')
                ->danger()
                ->icon('heroicon-o-x-circle')
                ->send();
            return;
        }

        if ($this->replicateAllMonths) {
            foreach ($this->monthStrings as $m) {
                CashFlow::updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'chart_of_account_id' => $accountUuid,
                        'month' => $m,
                    ],
                    [
                        'amount' => (float) $value,
                        'category' => 'projection',
                    ]
                );
                $this->cashFlowsMap[$accountUuid][$m] = (float) $value;
            }

            Notification::make()
                ->title('Valor replicado em todos os meses')
                ->success()
                ->icon('heroicon-o-check-circle')
                ->send();
        } else {
            CashFlow::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'chart_of_account_id' => $accountUuid,
                    'month' => $month,
                ],
                [
                    'amount' => (float) $value,
                    'category' => 'projection',
                ]
            );
            $this->cashFlowsMap[$accountUuid][$month] = (float) $value;

            Notification::make()
                ->title('Valor salvo com sucesso')
                ->success()
                ->icon('heroicon-o-check-circle')
                ->send();
        }
    }

    public $replicateAllMonths = false; // flag para replicar automaticamente
    
    public function fillAllMonths($accountUuid, $value)
    {
        foreach ($this->monthStrings as $month) {
            $this->updateCell($accountUuid, $month, $value);
        }

        Notification::make()
            ->title('Todos os meses foram preenchidos com sucesso.')
            ->success()
            ->icon('heroicon-o-check-circle')
            ->send();
    }

    public function loadCashFlowsMap()
    {
        $this->cashFlowsMap =[];

        $flows = CashFlow::where('company_id', Auth::user()->company_id)->get();

        foreach ($flows as $flow){
            $uuid = $flow-> chart_of_account_id;
            $month = $flow-> month;

            if ($flow->category === 'goal') {
                $this->cashFlowsMap[$uuid]['goal'] = $flow->amount;
            } else {
                $this->cashFlowsMap[$uuid][$month] = $flow->amount;
            }
        }
    }

    public function updateMetaCell(string $accountUuid, $rawValue): void
    {
        $value = $this->normalizeNumber($rawValue);
        if (!is_numeric($value)) {
            Notification::make()
                ->title('Valor inválido')
                ->danger()
                ->icon('heroicon-o-x-circle')
                ->send();
            return;
        }

        $companyId = Auth::user()->company_id;

        // salva ou atualiza o CashFlow da meta
        CashFlow::updateOrCreate(
            [
                'company_id' => $companyId,
                'chart_of_account_id' => $accountUuid,
                'month' => 'goal', // categoria 'goal' usa 'month' fixo
            ],
            [
                'amount' => (float) $value,
                'category' => 'goal',
            ]
        );

        // atualiza map interno para exibir na tabela
        $this->cashFlowsMap[$accountUuid]['goal'] = (float) $value;

        Notification::make()
            ->title('Meta salva com sucesso')
            ->success()
            ->icon('heroicon-o-check-circle')
            ->send();
    }

    public function updateInvestmentCell(string $month, $rawValue): void
    {
        if (!$this->investmentAccountUuid) {
            Notification::make()
                ->title('Selecione a conta de Investimento primeiro')
                ->warning()
                ->icon('heroicon-o-exclamation-triangle')
                ->send();
            return;
        }

        $value = $this->normalizeNumber($rawValue);
        if (!is_numeric($value)) {
            Notification::make()
                ->title('Valor inválido')
                ->danger()
                ->icon('heroicon-o-x-circle')
                ->send();
            return;
        }

        $companyId = Auth::user()->company_id ?? null;
        CashFlow::updateOrCreate(
            [
                'company_id' => $companyId,
                'chart_of_account_id' => $this->investmentAccountUuid,
                'month' => $month,
            ],
            [
                'amount' => (float) $value,
                'category' => 'investment',
            ]
        );

        $this->cashFlowsMap[$this->investmentAccountUuid][$month] = (float) $value;

        Notification::make()
            ->title('Investimento salvo com sucesso')
            ->success()
            ->icon('heroicon-o-check-circle')
            ->send();
    }

    public function calculateShortfall(?string $accountUuid, string $month): ?float
    {
        if (!$accountUuid) return null;

        // pega conta (para garantir que existe)
        $account = ChartOfAccount::find($accountUuid);
        if (!$account) return null;

        // 1) soma das entradas (receitas/projeções) para a conta e todos os descendentes
        $descendants = $account->getAllDescendantUuidsIncludingSelf();
        $entries = CashFlow::whereIn('chart_of_account_id', $descendants)
            ->where('month', $month)
            ->whereIn('category', ['projection', 'entrada'])
            ->sum('amount');

        // 2) pega somente a meta da conta atual (não soma metas das filhas)
        $meta = $this->cashFlowsMap[$accountUuid]['goal'] ?? 0;

        // 3) retorna diferença (positivo => receita > meta; negativo => falta atingir)
        return $entries - $meta;
    }

    protected function collectAllAccountsRecursively($accounts)
    {
        $all = collect();

        foreach ($accounts as $acc) {
            $all->push($acc);
            if ($acc->childAccounts->isNotEmpty()) {
                $all = $all->merge($this->collectAllAccountsRecursively($acc->childAccounts));
            }
        }

        return $all;
    }

    protected function normalizeNumber($raw)
    {
        if (is_null($raw)) return null;
        $s = (string) $raw;
        $s = str_replace(' ', '', trim($s));

        if (strpos($s, ',') !== false && strpos($s, '.') !== false && strrpos($s, ',') > strrpos($s, '.')) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } else {
            $s = str_replace(',', '.', $s);
        }

        return $s;
    }
    
    public function getParentSum($account, string $month): float
    {
        $sum =0;

        foreach($account->childAccounts as $child ){
            // pega valor do filho
            $val = $this->getCellValue($child->uuid, $month);

            //se o filho também tiver filho -> soma recursivamente

            if($child->childAccounts->isNotEmpty()){
                $val = $this->getParentSum($child, $month);
            }
            $sum += (float) $val;
        }
        return $sum;
    }
}