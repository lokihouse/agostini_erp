<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;

// <-- Importar Scope
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Para os logs
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

// <-- Importar Auth

class ProductionOrderItem extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id', // <-- Adicionar company_id aqui
        'production_order_uuid',
        'product_uuid',
        'production_step_uuid',
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

    public function productionStep(): BelongsTo
    {
        return $this->belongsTo(ProductionStep::class, 'production_step_uuid', 'uuid');
    }

    // --- FIM RELAÇÕES ---

    // --- MÉTODO BOOTED ---
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void // <-- ADICIONAR/COMPLETAR ESTE MÉTODO
    {
        // 1. Aplica o TenantScope globalmente para FILTRAR queries
        static::addGlobalScope(new TenantScope);

        // 2. Adiciona o listener 'creating' para DEFINIR company_id automaticamente
        static::creating(function (Model $model) {
            // Define company_id APENAS se ele ainda não estiver definido
            if (empty($model->company_id)) {
                // Tenta pegar da ordem pai primeiro (mais seguro)
                if ($model->productionOrder && $model->productionOrder->company_id) {
                    $model->company_id = $model->productionOrder->company_id;
                } // Se não conseguiu pela ordem, tenta pelo usuário logado
                elseif (Auth::check() && Auth::user()->company_id) {
                    $model->company_id = Auth::user()->company_id;
                }
                // Adicione lógica 'else' aqui se precisar lidar com outros cenários
            }
        });
    }
}
