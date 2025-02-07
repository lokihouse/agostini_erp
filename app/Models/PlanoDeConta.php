<?php

namespace App\Models;

use App\Models\Scopes\EmpresaScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([EmpresaScope::class])]
class PlanoDeConta extends ModelBase
{
    protected $with = ['subContas'];

    public function getValores(Carbon $dataInicial, Carbon $dataFinal)
    {
        $movs = $this->movimentacoes
            ->where('data_movimentacao', '>=', $dataInicial->format('Y-m-d'))
            ->where('data_movimentacao', '<=', $dataFinal->translatedFormat('Y-m-d'))
            ->toArray();

        $total = 0;

        foreach ($movs as $mov){
            if($mov['tipo'] === 'entrada'){
                $total += $mov['valor'];
            } else {
                $total -= $mov['valor'];
            }
        }

        return $total;
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function contaPai(): BelongsTo
    {
        return $this->belongsTo(PlanoDeConta::class, 'plano_de_conta_id');
    }

    public function subContas(): HasMany
    {
        return $this->hasMany(PlanoDeConta::class, 'plano_de_conta_id');
    }

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(Movimentacao::class);
    }
}
