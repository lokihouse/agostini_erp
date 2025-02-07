<?php

namespace App\Models;

use App\Models\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([EmpresaScope::class])]
class Movimentacao extends ModelBase
{
    protected $table = 'movimentacoes';

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function planoDeConta(): BelongsTo
    {
        return $this->belongsTo(PlanoDeConta::class);
    }
}
