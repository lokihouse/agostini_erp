<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class SalesGoal extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'user_id',
        'period',
        'goal_amount',
        'commission_type',
        'commission_percentage',
    ];

    protected $casts = [
        'commission_percentage' => 'decimal:2',
        'period' => 'date',
        'goal_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (Model $model) {
            if (empty($model->company_id) && Auth::check() && Auth::user()->company_id) {
                $model->company_id = Auth::user()->company_id;
            }
            // Garante que o 'period' seja sempre o primeiro dia do mês
            if ($model->period) {
                $model->period = \Carbon\Carbon::parse($model->period)->startOfMonth();
            }
        });

        static::updating(function (Model $model) {
            // Garante que o 'period' seja sempre o primeiro dia do mês ao atualizar
            if ($model->isDirty('period') && $model->period) {
                $model->period = \Carbon\Carbon::parse($model->period)->startOfMonth();
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'uuid');
    }

    public function user(): BelongsTo // Vendedor
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }
}

