<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth; // <-- Import the Auth facade

class ProductionOrder extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    const STATUS_COMPLETED = 'Concluída';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id', // Good, it's fillable
        'order_number',
        'due_date',
        'start_date',
        'completion_date',
        'status',
        'notes',
        'user_uuid', // Foreign key for the user
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
        'start_date' => 'datetime',
        'completion_date' => 'datetime',
    ];

    // --- RELATIONSHIPS ---

    /**
     * Get the company that owns the production order.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'uuid');
    }

    /**
     * Get the user responsible for the production order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    /**
     * Get the items associated with the production order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ProductionOrderItem::class, 'production_order_uuid', 'uuid');
    }

    public function productionOrderLogs(): HasMany
    {
        return $this->hasMany(ProductionOrderLog::class, 'production_order_uuid', 'uuid');
    }

    // --- END RELATIONSHIPS ---


    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (ProductionOrder $productionOrder) {
            if (empty($productionOrder->company_id)) {
                if (Auth::check() && Auth::user()->company_id) {
                    $productionOrder->company_id = Auth::user()->company_id;
                }
            }
        });
    }
}
