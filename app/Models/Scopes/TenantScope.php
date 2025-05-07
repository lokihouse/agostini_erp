<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * Aplica o filtro principal para buscar apenas registros da empresa do usuário logado.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check() && Auth::user()->company_id) {
            $builder->where($model->getTable().'.company_id', Auth::user()->company_id);
        }
    }

    /**
     * Extend the query builder with tenant-specific functionality.
     * REMOVA o listener 'creating' daqui.
     */
    public function extend(Builder $builder): void
    {
        // O listener 'creating' foi movido para o método booted() de cada Model.

        // Mantenha o macro se você o utiliza:
        $builder->macro('withoutTenantScope', function (Builder $builder) {
            // É mais seguro passar o nome da classe aqui
            return $builder->withoutGlobalScope(TenantScope::class);
        });

        // Você pode adicionar outros extensores aqui se necessário,
        // por exemplo, para impedir a alteração do company_id no 'updating'.
        // $builder->updating(function (Model $model) {
        //     if ($model->isDirty('company_id') && !is_null($model->getOriginal('company_id'))) {
        //          throw new \Exception("Changing the company_id is not allowed.");
        //     }
        // });
    }
}
