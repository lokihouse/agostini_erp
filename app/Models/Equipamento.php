<?php

namespace App\Models;

use App\Models\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([EmpresaScope::class])]
class Equipamento extends Model
{
    use HasFactory;

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }
}
