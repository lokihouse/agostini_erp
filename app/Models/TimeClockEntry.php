<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

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
        'status', // Adicionado
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
    public const TYPE_MANUAL_ENTRY = 'manual_entry'; // <<<---- ADICIONADO AQUI

    // Constantes para os status da batida
    public const STATUS_NORMAL = 'normal';
    public const STATUS_ALERT = 'alert';
    public const STATUS_JUSTIFIED = 'justified';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_ACCOUNTED = 'accounted';


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
            self::TYPE_MANUAL_ENTRY => 'Entrada Manual', // <<<---- ADICIONADO AQUI
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
