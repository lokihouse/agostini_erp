<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class EmpresaOrNullScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('empresa_id', auth()->user()->empresa_id)->orWhereNull('empresa_id');
    }
}
