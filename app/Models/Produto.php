<?php

namespace App\Models;

use App\Models\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([EmpresaScope::class])]
class Produto extends ModelBase
{
    protected $with = ['produto_etapas'];
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function produto_etapas(): HasMany
    {
        return $this->hasMany(ProdutoEtapa::class);
    }
}
