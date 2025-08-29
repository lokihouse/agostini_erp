<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Precisa importar BelongsToMany aqui!
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Importar BelongsTo
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
// Importar BelongsToMany
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Product extends Model
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
        'company_id', // <-- Adicionar company_id aqui
        'name',
        'sku',
        'description',
        'unit_of_measure',
        'standard_cost',
        'sale_price',
        'minimum_sale_price',
        'stock'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'standard_cost' => 'decimal:2', // Manter casts existentes
        'sale_price' => 'decimal:2',
        'minimum_sale_price' => 'decimal:2',
    ];

    // --- RELAÇÕES ---

    /**
     * Get the company that owns the product.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'uuid');
    }

    /**
     * Get the production order items associated with the product.
     */
    public function productionOrderItems(): HasMany
    {
        return $this->hasMany(ProductionOrderItem::class, 'product_uuid', 'uuid');
    }

    /**
     * Define o relacionamento Muitos-para-Muitos com ProductionStep.
     * ESTE MÉTODO PRECISA ESTAR AQUI!
     */
    public function productionSteps(): BelongsToMany // <-- Método necessário
    {
        return $this->belongsToMany(
            ProductionStep::class,      // Modelo relacionado
            'product_production_step',  // Nome da tabela pivot (igual à migration)
            'product_uuid',             // Chave estrangeira deste modelo na pivot
            'production_step_uuid'      // Chave estrangeira do modelo relacionado na pivot
        )
            ->withPivot('step_order') // <-- Informa sobre a coluna extra 'step_order'
            ->orderBy('step_order');  // <-- Opcional: Ordena as etapas
    }


    // --- FIM RELAÇÕES ---


    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model) {
            if (empty($model->company_id)) {
                if (Auth::check() && Auth::user()->company_id) {
                    $model->company_id = Auth::user()->company_id;
                }
            }
        });
    }
}
