<?php

namespace App\Models;

use App\Http\Controllers\MovimentacaoFinanceiraController;
use App\Models\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([EmpresaScope::class])]
class MovimentacaoFinanceira extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::created(function($model){
            MovimentacaoFinanceiraController::atualizarSaldo($model);
        });
    }

    protected $fillable = [
        'empresa_id',
        'plano_de_conta_id',
        'descricao',
        'natureza',
        'valor',
    ];

    protected $appends = ['descricao_completa'];

    public function getDescricaoCompletaAttribute()
    {
        return $this->codigo . ' - ' . $this->descricao;
    }

    public function planoDeConta()
    {
        return $this->belongsTo(PlanoDeConta::class);
    }
}
