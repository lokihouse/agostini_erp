<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends ModelBase
{
    protected $fillable = ['ativo', 'cnpj', 'razao_social', 'nome_fantasia'];
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function produtos(): HasMany
    {
        return $this->hasMany(Produto::class);
    }

    public function jornadas_de_trabalho(): HasMany
    {
        return $this->hasMany(JornadaDeTrabalho::class);
    }
}
