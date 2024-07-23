<?php

namespace App\Models;

use App\Http\Controllers\PlanoDeContaController;
use App\Models\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([EmpresaScope::class])]
class PlanoDeConta extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::created(function($model){
            if($model->movimentacao && $model->valor_projetado){
                PlanoDeContaController::atualizarValorProjetado($model);
            }
        });

        self::deleted(function($model){
            if($model->movimentacao && $model->valor_projetado){
                $model->valor_projetado = -1 * $model->valor_projetado;
                PlanoDeContaController::atualizarValorProjetado($model);
            }
        });
    }

    protected $fillable = [
        'empresa_id',
        'plano_de_conta_id',
        'codigo',
        'descricao',
        'status',
        'movimentacao',
        'data_inicio',
        'data_fim',
        'valor_projetado',
        'valor_realizado',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function planoDeContaPai()
    {
        return $this->belongsTo(PlanoDeConta::class, 'plano_de_conta_id');
    }

    public function planoDeContasFilhos()
    {
        return $this->hasMany(PlanoDeConta::class);
    }

    public function movimentacoes()
    {
        return $this->hasMany(MovimentacaoFinanceira::class);
    }
}
