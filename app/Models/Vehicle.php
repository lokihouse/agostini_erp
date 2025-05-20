<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Vehicle extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'license_plate',
        'description',
        'brand',
        'model_name',
        'year_manufacture',
        'year_model',
        'color',
        'cargo_volume_m3',
        'max_load_kg',
        'renavam',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'cargo_volume_m3' => 'decimal:3',
        'max_load_kg' => 'decimal:2',
        'year_manufacture' => 'integer', // Ou string se preferir
        'year_model' => 'integer', // Ou string se preferir
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (Model $model) {
            if (empty($model->company_id) && Auth::check() && Auth::user()->company_id) {
                $model->company_id = Auth::user()->company_id;
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'uuid');
    }

    // Relacionamento com Ordens de Transporte (se um veículo pode ter muitas)
    public function transportOrders()
    {
        return $this->hasMany(TransportOrder::class, 'vehicle_id', 'uuid');
    }
}

