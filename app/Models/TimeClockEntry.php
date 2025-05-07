<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeClockEntry extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    // Definindo constantes para os status
    public const STATUS_NORMAL = 'normal';
    public const STATUS_ALERT = 'alert';
    public const STATUS_JUSTIFIED = 'justified';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_ACCOUNTED = 'accounted';

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

    // Adicionar o status ao $attributes default para garantir que tenha um valor ao criar
    protected $attributes = [
        'status' => self::STATUS_NORMAL,
    ];

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

    public static function getEntryTypeOptions(): array
    {
        return [
            'clock_in' => 'Entrada',
            'clock_out' => 'Saída',
            'start_break' => 'Início Pausa',
            'end_break' => 'Fim Pausa',
            'manual_entry' => 'Entrada Manual',
        ];
    }

    public function getEntryTypeLabelAttribute(): string
    {
        return self::getEntryTypeOptions()[$this->type] ?? $this->type;
    }

    // Método para obter as opções de status
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

    // Acessor para o label do status
    public function getStatusLabelAttribute(): string
    {
        return self::getStatusOptions()[$this->status] ?? ucfirst($this->status);
    }
}
