<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkShiftDay extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'work_shift_uuid',
        'day_of_week',
        'is_off_day',
        'starts_at',
        'ends_at',
        'interval_starts_at',
        'interval_ends_at',
    ];

    protected $casts = [
        'is_off_day' => 'boolean',
        'day_of_week' => 'integer',
    ];

    public function workShift(): BelongsTo
    {
        return $this->belongsTo(WorkShift::class, 'work_shift_uuid', 'uuid');
    }

    // Helper para obter o nome do dia (opcional)
    public function getDayNameAttribute(): string
    {
        $days = [
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
            7 => 'Domingo',
        ];
        return $days[$this->day_of_week] ?? 'Desconhecido';
    }
}
