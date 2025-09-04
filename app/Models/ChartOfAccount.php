<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChartOfAccount extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
        'parent_uuid',
    ];

    protected $casts = [
        // Se necessário, adicione casts
    ];

    public const TYPE_ASSET = 'asset';
    public const TYPE_LIABILITY = 'liability';
    public const TYPE_EQUITY = 'equity';
    public const TYPE_REVENUE = 'revenue';
    public const TYPE_EXPENSE = 'expense';

    public static function getTypeOptions(): array
    {
        return [
            self::TYPE_ASSET => 'Ativo',
            self::TYPE_LIABILITY => 'Passivo',
            self::TYPE_EQUITY => 'Patrimônio Líquido',
            self::TYPE_REVENUE => 'Receita',
            self::TYPE_EXPENSE => 'Despesa',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (Model $model) {
            if (empty($model->company_id) && Auth::check() && Auth::user()->company_id) {
                $model->company_id = Auth::user()->company_id;
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'uuid');
    }

    public function parentAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_uuid', 'uuid');
    }

    public function childAccounts(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_uuid', 'uuid');
    }

    public function financialTransactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class, 'chart_of_account_uuid', 'uuid');
    }

    private function collectDescendantUuids(self $account, array &$uuids): void
    {
        foreach ($account->childAccounts as $child) {
            $uuids[] = $child->uuid;
            $this->collectDescendantUuids($child, $uuids);
        }
    }

    public function getAllDescendantUuidsIncludingSelf(): array
    {
        $uuids = [$this->uuid];
        // Ensure childAccounts are loaded before starting recursion
        $this->loadMissing('childAccounts');
        $this->collectDescendantUuids($this, $uuids);
        return array_unique($uuids);
    }

    /**
     * Calculates the sum of financial transactions for this account and its descendants
     * within a given period.
     * Income is positive, Expense is negative.
     */
   public function getValuesForPeriod(Carbon $startDate, Carbon $endDate, ?string $tipo = null): float
    {
    $accountUuids = $this->getAllDescendantUuidsIncludingSelf();
    $query = FinancialTransaction::query()
        ->whereIn('chart_of_account_uuid', $accountUuids)
        ->whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()]);

    if ($tipo === 'entrada') {
        $query->where('type', FinancialTransaction::TYPE_INCOME);
        $total = $query->sum('amount');
    } elseif ($tipo === 'saida') {
        $query->where('type', FinancialTransaction::TYPE_EXPENSE);
        $total = $query->sum('amount');
    } else {
        // saldo líquido: entradas positivas, saídas negativas
        $total = $query->sum(DB::raw("
            CASE 
                WHEN type = '" . FinancialTransaction::TYPE_INCOME . "' THEN amount 
                ELSE -amount 
            END
        "));
    }
    // como os valores estão em centavos, normalizamos para reais
    return (float) ($total / 100);
}  
}
