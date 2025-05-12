<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCurrentTask extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'user_current_tasks'; // Nome da tabela

    protected $primaryKey = 'uuid'; // Define a chave primária
    public $incrementing = false; // Indica que a chave primária não é auto-incremento
    protected $keyType = 'string'; // Tipo da chave primária

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_uuid',
        'production_order_item_uuid',
        'production_step_uuid',
        'work_slot_uuid',
        'status',
        'started_at',
        'last_resumed_at',
        'last_pause_at',
        'last_pause_reason_uuid',
        'total_active_seconds',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'last_resumed_at' => 'datetime',
        'last_pause_at' => 'datetime',
        'total_active_seconds' => 'integer',
    ];

    // --- RELACIONAMENTOS ---

    /**
     * Get the user performing the task.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    /**
     * Get the production order item being worked on.
     */
    public function productionOrderItem(): BelongsTo
    {
        return $this->belongsTo(ProductionOrderItem::class, 'production_order_item_uuid', 'uuid');
    }

    /**
     * Get the production step being performed.
     */
    public function productionStep(): BelongsTo
    {
        return $this->belongsTo(ProductionStep::class, 'production_step_uuid', 'uuid');
    }

    /**
     * Get the work slot where the task is being performed (optional).
     */
    public function workSlot(): BelongsTo
    {
        return $this->belongsTo(WorkSlot::class, 'work_slot_uuid', 'uuid');
    }

    public function lastPauseReasonDetail(): BelongsTo // NOVO RELACIONAMENTO
    {
        return $this->belongsTo(PauseReason::class, 'last_pause_reason_uuid', 'uuid');
    }

    // --- FIM RELACIONAMENTOS ---

    // --- MÉTODOS AUXILIARES (Exemplos - podem ser adicionados depois) ---

    /**
     * Verifica se a tarefa está ativa.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Verifica se a tarefa está pausada.
     */
    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

}
