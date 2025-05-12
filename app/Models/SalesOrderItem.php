<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Para DB::raw

class SalesOrderItem extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'sales_order_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount_amount',
        'final_price',
        'total_price',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (Model $model) {
            if (empty($model->company_id)) {
                if ($model->salesOrder && $model->salesOrder->company_id) {
                    $model->company_id = $model->salesOrder->company_id;
                } elseif (Auth::check() && Auth::user()->company_id) {
                    $model->company_id = Auth::user()->company_id;
                }
            }
            // Calcula final_price e total_price antes de salvar
            $model->calculatePrices();
        });

        static::updating(function (Model $model) {
            // Recalcula se campos relevantes mudarem
            if ($model->isDirty('quantity') || $model->isDirty('unit_price') || $model->isDirty('discount_amount')) {
                $model->calculatePrices();
            }
        });

        // Após salvar (created ou updated), atualiza o total do pedido pai
        static::saved(function (SalesOrderItem $item) {
            $item->salesOrder?->updateTotalAmount();
        });

        // Após deletar, atualiza o total do pedido pai
        static::deleted(function (SalesOrderItem $item) {
            $item->salesOrder?->updateTotalAmount();
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'uuid');
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id', 'uuid');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'uuid');
    }

    /**
     * Calcula final_price e total_price.
     */
    public function calculatePrices(): void
    {
        $this->final_price = ($this->unit_price ?? 0) - ($this->discount_amount ?? 0);
        if ($this->final_price < 0) { // Garante que o preço final não seja negativo
            $this->final_price = 0;
        }
        $this->total_price = ($this->quantity ?? 0) * $this->final_price;
    }
}
