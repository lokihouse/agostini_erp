<?php

namespace App\Models;

use App\Models\Scopes\EmpresaOrNullScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([EmpresaOrNullScope::class])]
class Calendario extends ModelBase
{
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
