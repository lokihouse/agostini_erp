<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // <-- Importar BelongsToMany
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth; // <-- Importar Auth

class WorkSlot extends Model
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
        'company_id',
        'name',
        'description',
        'location',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // --- RELAÇÕES ---

    /**
     * Get the company that owns the work slot.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'uuid');
    }

    /**
     * Get the production logs associated with this work slot.
     */
    public function productionLogs(): HasMany // Renomear se houver conflito
    {
        return $this->hasMany(ProductionLog::class, 'work_slot_uuid', 'uuid');
    }

    /**
     * The production steps that can be performed at this work slot.
     */
    public function productionSteps(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductionStep::class,
            'production_step_work_slot', // Pivot table name
            'work_slot_uuid',            // Foreign key on pivot table for this model
            'production_step_uuid'       // Foreign key on pivot table for the related model
        );
    }

    // --- FIM RELAÇÕES ---

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void // <-- CORRIGIR ESTE MÉTODO
    {
        // 1. Aplica o TenantScope globalmente para FILTRAR queries
        static::addGlobalScope(new TenantScope());

        // 2. Adiciona o listener 'creating' para DEFINIR company_id automaticamente
        static::creating(function (Model $model) { // <-- ADICIONAR ESTE BLOCO
            // Define company_id APENAS se ele ainda não estiver definido
            if (empty($model->company_id)) {
                // Verifica se há usuário logado e com empresa
                if (Auth::check() && Auth::user()->company_id) {
                    $model->company_id = Auth::user()->company_id;
                }
                // Adicione lógica 'else' aqui se precisar lidar com outros cenários
            }
        });

        // Opcional: Listener para impedir mudança de company_id no update
        // static::updating(function (Model $model) {
        //     if ($model->isDirty('company_id') && $model->getOriginal('company_id') !== null) {
        //         $model->company_id = $model->getOriginal('company_id');
        //     }
        // });
    }
}
