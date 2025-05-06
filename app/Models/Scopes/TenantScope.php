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
        // Verifica se há um usuário autenticado e se ele tem um company_id
        if (Auth::check() && Auth::user()->company_id) {
            // Adiciona a cláusula WHERE para filtrar pela company_id do usuário
            $builder->where($model->getTable().'.company_id', Auth::user()->company_id);
        }
        // Adicione aqui a lógica 'else' se precisar lidar com console/super-admins
        // Exemplo: impedir retorno de dados em contexto web sem usuário/empresa
        // else {
        //     if (!app()->runningInConsole() && Auth::guest()) { // Verifica se não está no console E não há usuário logado
        //          $builder->whereRaw('1 = 0'); // Nunca retorna resultados
        //     }
        //     // Ou: if (Auth::check() && Auth::user()->hasRole('Super Admin')) { /* não aplica filtro */ }
        // }
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
