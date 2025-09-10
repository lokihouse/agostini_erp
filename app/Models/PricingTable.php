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
class PricingTable extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pricing_table';

    protected $fillable = [
        'product_id',
        'custo_materia_prima',
        'despesas',
        'imposto',
        'comissao',
        'frete',
        'prazo',
        'vpc',
        'inadimplencia',
        'assistencia',
        'lucro',
        'valorDespesas',
        'valorImposto',
        'valorComissao',
        'valorFrete',
        'valorPrazo',
        'valorVPC',
        'valorInadimplencia',
        'valorAssistencia',
        'custoProduto',
        'indice_preco',
        'comercializacao',
        'lucro_total',
        'preco_final',
        'company_id' // 👈 importante
    ];

    protected static function booted()
    {
    // Preenche automaticamente company_id ao criar
    static::creating(function ($model) {
        if (Auth::check() && empty($model->company_id)) {
            $model->company_id = Auth::user()->company_id;
        }
    });

    // Scope global para filtrar registros da empresa do usuário logado
    static::addGlobalScope('company', function (\Illuminate\Database\Eloquent\Builder $builder) {
        if (Auth::check()) {
            $builder->where('company_id', Auth::user()->company_id);
        }
    });

    static::saving(function ($model) {
        $valor_despesas = ($model->despesas / 100) * $model->custo_materia_prima;
        
        $model->custo_produto = $model->custo_materia_prima + $valor_despesas;

            $somaPercentuais = (
                $model->imposto +
                $model->comissao +
                $model->frete +
                $model->prazo +
                $model->vpc +
                $model->assistencia +
                $model->inadimplencia +
                $model->lucro
            ) / 100;

            $model->indice_preco = 1 / (1 - $somaPercentuais);
            $model->preco_final = $model->custo_produto * $model->indice_preco;

            // valores de cada item
            $model->valorImposto = ($model->imposto / 100) * $model->preco_final;
            $model->valorComissao = ($model->comissao / 100) * $model->preco_final;
            $model->valorFrete = ($model->frete / 100) * $model->preco_final;
            $model->valorPrazo = ($model->prazo / 100) * $model->preco_final;
            $model->valorVPC = ($model->vpc / 100) * $model->preco_final;
            $model->valorAssistencia = ($model->assistencia / 100) * $model->preco_final;
            $model->valorInadimplencia = ($model->inadimplencia / 100) * $model->preco_final;
            $model->valorDespesas = ($model->despesas / 100) * $model->custo_materia_prima;

            $model->comercializacao = (
                ($model->valorImposto +
                $model->valorComissao +
                $model->valorFrete +
                $model->valorPrazo +
                $model->valorVPC +
                $model->valorAssistencia +
                $model->valorInadimplencia)
            );

            $model->lucro_total = ($model->lucro / 100) * $model->preco_final;
        });
    }
    
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'uuid');
    }
}
