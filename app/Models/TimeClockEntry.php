<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException; // <<<---- ADICIONADO AQUI

class TimeClockEntry extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'company_id',
        'recorded_at',
        'type',
        'status',
        'latitude',
        'longitude',
        'ip_address',
        'user_agent',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'approved_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    // Constantes para os tipos de batida
    public const TYPE_CLOCK_IN = 'clock_in';
    public const TYPE_CLOCK_OUT = 'clock_out';
    public const TYPE_START_BREAK = 'start_break';
    public const TYPE_END_BREAK = 'end_break';
    public const TYPE_MANUAL_ENTRY = 'manual_entry';

    // Constantes para os status da batida
    public const STATUS_NORMAL = 'normal';
    public const STATUS_ALERT = 'alert';
    public const STATUS_JUSTIFIED = 'justified';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_ACCOUNTED = 'accounted';

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void // <<<---- ADICIONADO AQUI
    {
        static::creating(function (TimeClockEntry $entry) {
            // Garante que recorded_at seja um objeto Carbon para validação
            if (is_string($entry->recorded_at)) {
                $entry->recorded_at = Carbon::parse($entry->recorded_at);
            }
            self::validateUniqueness($entry);
        });

        static::updating(function (TimeClockEntry $entry) {
            // Garante que recorded_at seja um objeto Carbon para validação
            if (is_string($entry->recorded_at)) {
                $entry->recorded_at = Carbon::parse($entry->recorded_at);
            }
            // Validar apenas se os campos relevantes para a unicidade foram alterados
            if ($entry->isDirty('user_id') || $entry->isDirty('recorded_at')) {
                self::validateUniqueness($entry, $entry->getKey()); // Usa getKey() para obter o UUID
            }
        });
    }

    /**
     * Valida se já existe uma batida de ponto para o mesmo usuário e data/hora exata.
     * Lança uma ValidationException se uma duplicata for encontrada.
     *
     * @param TimeClockEntry $entry
     * @param string|null $excludeId UUID do registro a ser excluído da verificação (para atualizações)
     * @return void
     * @throws ValidationException
     */
    public static function validateUniqueness(TimeClockEntry $entry, ?string $excludeId = null): void // <<<---- ADICIONADO AQUI
    {
        if (!$entry->user_id || !$entry->recorded_at instanceof Carbon) {
            // Não prosseguir se os dados essenciais não estiverem presentes ou no formato esperado
            // Isso pode indicar um problema anterior no fluxo de dados.
            // Você pode querer logar isso ou lançar uma exceção diferente.
            return;
        }

        $query = static::where('user_id', $entry->user_id)
            // Compara o timestamp completo. Se precisar de granularidade de segundos:
            ->where('recorded_at', $entry->recorded_at->format('Y-m-d H:i:s'));
        // Se a intenção é apenas dia e hora (sem segundos), ajuste o formato e a comparação.
        // Exemplo para dia e hora (HH:MM):
        // ->whereDate('recorded_at', $entry->recorded_at->toDateString())
        // ->whereTime('recorded_at', $entry->recorded_at->format('H:i:00')); // Zera os segundos para comparação

        if ($excludeId) {
            $query->where((new static())->getKeyName(), '!=', $excludeId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                // A chave 'recorded_at' pode ser usada para exibir o erro no campo do formulário
                // se o nome do campo no formulário for 'recorded_at'.
                // Ajuste conforme o nome do campo no seu formulário Livewire.
                'recorded_at' => 'Já existe uma batida de ponto para este usuário neste exato dia e horário.',
                // Alternativamente, uma chave mais genérica:
                // 'duplicate_entry' => 'Já existe uma batida de ponto para este usuário neste exato dia e horário.',
            ]);
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'uuid');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'uuid');
    }

    /**
     * Retorna um array com as opções de tipo de batida para selects.
     */
    public static function getEntryTypeOptions(): array
    {
        return [
            self::TYPE_CLOCK_IN => 'Entrada',
            self::TYPE_CLOCK_OUT => 'Saída',
            self::TYPE_START_BREAK => 'Início da Pausa',
            self::TYPE_END_BREAK => 'Fim da Pausa',
            self::TYPE_MANUAL_ENTRY => 'Entrada Manual',
        ];
    }

    /**
     * Retorna um array com as opções de status para selects.
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_NORMAL => 'Normal',
            self::STATUS_ALERT => 'Alerta',
            self::STATUS_JUSTIFIED => 'Justificada',
            self::STATUS_APPROVED => 'Aprovada',
            self::STATUS_ACCOUNTED => 'Contabilizada',
        ];
    }

    /**
     * Acessor para obter o label do status.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatusOptions()[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Acessor para obter o label do tipo de entrada.
     */
    public function getEntryTypeLabelAttribute(): string
    {
        return self::getEntryTypeOptions()[$this->type] ?? ucfirst($this->type);
    }
}
