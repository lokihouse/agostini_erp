<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory;
    use HasUuids;
    use Notifiable;
    use HasRoles;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'work_shift_id', // Adicionado
        'name',
        'username',
        'password',
        'is_active'
    ];

    protected $hidden = [
        'password'
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean'
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'uuid');
    }

    public function timeClockEntries(): HasMany
    {
        return $this->hasMany(TimeClockEntry::class, 'user_id', 'uuid');
    }

    // Nova Relação
    public function workShift(): BelongsTo
    {
        return $this->belongsTo(WorkShift::class, 'work_shift_id', 'uuid');
    }
}

