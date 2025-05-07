<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkShift extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'name',
        'type',
        'notes',
        'cycle_work_duration_hours',
        'cycle_off_duration_hours',
        'cycle_shift_starts_at',
        'cycle_shift_ends_at',
        'cycle_interval_starts_at',
        'cycle_interval_ends_at',
    ];

    protected $casts = [
        'cycle_work_duration_hours' => 'integer',
        'cycle_off_duration_hours' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'uuid');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'work_shift_id', 'uuid');
    }


    public function workShiftDays(): HasMany
    {
        return $this->hasMany(WorkShiftDay::class, 'work_shift_uuid', 'uuid');
    }
}
