<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Import já existe, ótimo
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth; // Import já existe, ótimo

class ProductionStep extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'default_order',
    ];

    protected $casts = [
        'default_order' => 'integer',
    ];

    // --- RELAÇÕES ---

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'uuid');
    }

    public function workSlots(): BelongsToMany
    {
        return $this->belongsToMany(
            WorkSlot::class,
            'production_step_work_slot',
            'production_step_uuid',
            'work_slot_uuid'
        );
    }

    /**
     * Define o relacionamento Muitos-para-Muitos INVERSO com Product.
     * ESTE MÉTODO ESTAVA FALTANDO!
     */
    public function products(): BelongsToMany // <-- Adicionar este método
    {
        return $this->belongsToMany(
            Product::class,                 // Modelo relacionado
            'product_production_step',      // Nome da tabela pivot (igual à migration)
            'production_step_uuid',         // Chave estrangeira DESTE modelo (ProductionStep) na pivot
            'product_uuid'                  // Chave estrangeira DO OUTRO modelo (Product) na pivot
        )
            ->withPivot('step_order') // <-- Informa sobre a coluna extra 'step_order'
            ->withTimestamps(); // <-- Opcional: se a tabela pivot tiver timestamps
    }

    // --- FIM RELAÇÕES ---

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (Model $model) {
            if (empty($model->company_id)) {
                if (Auth::check() && Auth::user()->company_id) {
                    $model->company_id = Auth::user()->company_id;
                }
            }
        });
    }
}
