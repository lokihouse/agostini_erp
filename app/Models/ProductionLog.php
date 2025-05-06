<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Importar BelongsTo
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionLog extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id', // <-- Adicionar company_id aqui
        'production_order_item_uuid',
        'production_step_uuid',
        'work_slot_uuid',
        'user_uuid',
        'quantity',
        'log_time',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:4', // Manter casts existentes
        'log_time' => 'datetime',
    ];

    // --- RELAÇÕES ---

    /**
     * Get the company that owns this log entry (indirectly, but good for consistency).
     * ESTE É O MÉTODO QUE PRECISAMOS ADICIONAR!
     */
    public function company(): BelongsTo // <-- Adicionar este método
    {
        // Chave estrangeira nesta tabela ('production_logs'), chave primária na tabela pai ('companies')
        return $this->belongsTo(Company::class, 'company_id', 'uuid');
    }

    /**
     * Get the production order item associated with this log.
     */
    public function productionOrderItem(): BelongsTo // <-- Relação com ProductionOrderItem
    {
        return $this->belongsTo(ProductionOrderItem::class, 'production_order_item_uuid', 'uuid');
    }

    /**
     * Get the production step associated with this log.
     */
    public function productionStep(): BelongsTo // <-- Relação com ProductionStep
    {
        return $this->belongsTo(ProductionStep::class, 'production_step_uuid', 'uuid');
    }

    /**
     * Get the work slot where this log occurred (if applicable).
     */
    public function workSlot(): BelongsTo // <-- Relação com WorkSlot
    {
        // Note que work_slot_uuid pode ser nulo na migration, então esta relação pode retornar null
        return $this->belongsTo(WorkSlot::class, 'work_slot_uuid', 'uuid');
    }

    /**
     * Get the user who created this log entry.
     */
    public function user(): BelongsTo // <-- Relação com User
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }
}
