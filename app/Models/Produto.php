<?php

namespace App\Models;

use App\Models\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([EmpresaScope::class])]
class Produto extends ModelBase
{
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
