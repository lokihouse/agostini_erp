<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // Adicionar importação do Log

class TaskPauseLog extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_current_task_uuid',
        'production_order_item_uuid',
        'pause_reason_uuid',
        'user_uuid',
        'paused_at',
        'resumed_at',
        'duration_seconds',
        'quantity_produced_during_pause',
        'notes',
    ];

    protected $casts = [
        'paused_at' => 'datetime',
        'resumed_at' => 'datetime',
        'duration_seconds' => 'integer',
        'quantity_produced_during_pause' => 'decimal:4',
    ];

    /**
     * Relacionamento com a tarefa atual do usuário.
     */
    public function userCurrentTask(): BelongsTo
    {
        return $this->belongsTo(UserCurrentTask::class, 'user_current_task_uuid', 'uuid');
    }

    /**
     * Relacionamento com o motivo da pausa.
     */
    public function pauseReason(): BelongsTo
    {
        return $this->belongsTo(PauseReason::class, 'pause_reason_uuid', 'uuid');
    }

    /**
     * Relacionamento com o usuário.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    /**
     * Calcula e define a duração da pausa quando resumed_at é definido.
     */
    protected static function booted(): void
    {
        static::saving(function (TaskPauseLog $log) {
            // Calcula duração somente se resumed_at estiver sendo definido (ou alterado)
            // e duration_seconds ainda não foi calculado.
            if ($log->paused_at && $log->resumed_at && $log->isDirty('resumed_at') && is_null($log->getOriginal('duration_seconds'))) {
                $paused = Carbon::parse($log->paused_at);
                $resumed = Carbon::parse($log->resumed_at);

                // diffInSeconds($date, $abs = true) -> o segundo parâmetro $abs não controla o sinal do retorno de diffInSeconds diretamente.
                // diffInSeconds sempre retorna um valor com sinal.
                $signedDuration = $resumed->diffInSeconds($paused, false); // Garante que pegamos a diferença com sinal

                if ($signedDuration < 0) {
                    // Esta situação (resumed_at < paused_at) é inesperada para uma duração de pausa.
                    // Registra um aviso e usa o valor absoluto.
                    Log::warning('TaskPauseLog: resumed_at é anterior a paused_at. A duração será armazenada como valor absoluto.', [
                        'task_pause_log_uuid' => $log->uuid,
                        'user_current_task_uuid' => $log->user_current_task_uuid,
                        'paused_at' => $log->paused_at instanceof Carbon ? $log->paused_at->toDateTimeString() : $log->paused_at,
                        'resumed_at' => $log->resumed_at instanceof Carbon ? $log->resumed_at->toDateTimeString() : $log->resumed_at,
                        'calculated_signed_duration' => $signedDuration,
                    ]);
                    $log->duration_seconds = abs($signedDuration);
                } else {
                    $log->duration_seconds = $signedDuration;
                }
            }
        });
    }
}
