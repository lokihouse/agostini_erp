<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class FinancialTransaction extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'chart_of_account_uuid',
        'description',
        'amount',
        'type',
        'transaction_date',
        'user_id',
        'notes',
        // Adicione outros campos fillable conforme a migration
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public const TYPE_INCOME = 'income';
    public const TYPE_EXPENSE = 'expense';

    public static function getTypeOptions(): array
    {
        return [
            self::TYPE_INCOME => 'Entrada', // Ou Receita
            self::TYPE_EXPENSE => 'Saída',  // Ou Despesa
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (Model $model) {
            if (empty($model->company_id) && Auth::check() && Auth::user()->company_id) {
                $model->company_id = Auth::user()->company_id;
            }
            // Opcional: preencher user_id automaticamente se não fornecido
            if (empty($model->user_id) && Auth::check()) {
                $model->user_id = Auth::id();
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'uuid');
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_uuid', 'uuid');
    }

    public function user(): BelongsTo // Usuário que registrou
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }
}
