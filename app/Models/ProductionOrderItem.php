<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;

// <-- Importar Scope
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Para os logs
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

use App\Models\ProductionOrderItemStep;

// <-- Importar Auth

class ProductionOrderItem extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'production_order_uuid',
        'product_uuid',
        'quantity_planned',
        'quantity_produced',
        'notes',
    ];

    protected $casts = [
        'quantity_planned' => 'decimal:4', // Manter precisão da migration
        'quantity_produced' => 'decimal:4', // Manter precisão da migration
    ];

    // --- RELAÇÕES ---

    /**
     * Get the company that owns the production order item.
     * (Redundante, mas útil para consistência e queries diretas)
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'uuid');
    }

    /**
     * Get the production order this item belongs to.
     */
    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_uuid', 'uuid');
    }

    /**
     * Get the product associated with this item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    /**
     * Get the production logs for this specific order item.
     */
    public function productionLogs(): HasMany
    {
        return $this->hasMany(ProductionLog::class, 'production_order_item_uuid', 'uuid');
    }

    public function productionSteps(): BelongsToMany
    {
        return $this->belongsToMany(ProductionStep::class, 'production_order_item_steps', 'production_order_item_uuid', 'production_step_uuid')
            ->using(ProductionOrderItemStep::class)
            ->withTimestamps();
    }
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model) {
            if (empty($model->company_id)) {
                if ($model->productionOrder && $model->productionOrder->company_id) {
                    $model->company_id = $model->productionOrder->company_id;
                }
                elseif (Auth::check() && Auth::user()->company_id) {
                    $model->company_id = Auth::user()->company_id;
                }
            }
        });
    }
}
