<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth; // <-- Import the Auth facade

class ProductionOrder extends Model
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
        'company_id', // Good, it's fillable
        'order_number',
        'due_date',
        'start_date',
        'completion_date',
        'status',
        'notes',
        'user_uuid', // Foreign key for the user
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
        'start_date' => 'datetime',
        'completion_date' => 'datetime',
    ];

    // --- RELATIONSHIPS ---

    /**
     * Get the company that owns the production order.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'uuid');
    }

    /**
     * Get the user responsible for the production order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'uuid');
    }

    /**
     * Get the items associated with the production order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ProductionOrderItem::class, 'production_order_uuid', 'uuid');
    }

    // --- END RELATIONSHIPS ---


    /**
     * The "booted" method of the model.
     * Applies global scopes and registers model event listeners.
     */
    protected static function booted(): void
    {
        // 1. Apply the TenantScope globally to FILTER queries
        static::addGlobalScope(new TenantScope());

        // 2. Add the 'creating' listener to automatically SET company_id
        static::creating(function (Model $model) {
            // Only set company_id if it's not already set (e.g., by a seeder/factory)
            if (empty($model->company_id)) {
                // Check if a user is authenticated and has a company_id
                if (Auth::check() && Auth::user()->company_id) {
                    $model->company_id = Auth::user()->company_id;
                }
                // Optional: Add error handling or default logic if company_id
                // cannot be determined in a specific context (e.g., console commands
                // without explicit company context). For web requests, this
                // usually implies an unauthenticated user trying to create data.
            }
        });

        // Optional: Prevent changing the company_id after creation
        // static::updating(function (Model $model) {
        //     if ($model->isDirty('company_id') && $model->getOriginal('company_id') !== null) {
        //         // Revert the change or throw an exception
        //         $model->company_id = $model->getOriginal('company_id');
        //         // or: throw new \LogicException("Changing the company_id is forbidden.");
        //     }
        // });
    }
}
