<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashFlow extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'cash_flows';
    protected $primaryKey = 'uuid';   // <- chave primária correta
    public $incrementing = false;     // <- não é auto increment
    protected $keyType = 'string';    // <- tipo da chave é string

    protected $fillable = [
        'uuid',
        'chart_of_account_id',
        'company_id',
        'category',
        'month',
        'amount',
    ];

    protected static function booted()
    {
        // Preenche automaticamente company_id ao criar
        static::creating(function ($model) {
            if (Auth::check() && empty($model->company_id)) {
                $model->company_id = Auth::user()->company_id;
            }
        });

        // Scope global para filtrar registros da empresa do usuário logado
        static::addGlobalScope('company', function (\Illuminate\Database\Eloquent\Builder $builder) {
            if (Auth::check()) {
                $builder->where('company_id', Auth::user()->company_id);
            }
        });
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id', 'uuid');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
