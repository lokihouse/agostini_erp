<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Holiday extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'name',
        'date',
        'type',
        'is_recurrent',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurrent' => 'boolean',
    ];

    /**
     * Get the company that owns the holiday (if it's company-specific).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'uuid');
    }

    /**
     * Accessor to determine if the holiday is global.
     */
    public function getIsGlobalAttribute(): bool
    {
        return is_null($this->company_id);
    }
}
