<?php

namespace App\Models;

use App\Models\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([EmpresaScope::class])]
class OrdemDeTransporteEntrega extends ModelBase
{
    public function ordem_de_transporte(): BelongsTo
    {
        return $this->belongsTo(OrdemDeTransporte::class);
    }
}
