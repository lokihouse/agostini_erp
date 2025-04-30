<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventosPorOrdemDeProducao extends Model
{
    protected $table = 'eventos_por_ordem_de_producao';

    public function ordem_de_producao(): BelongsTo
    {
        return $this->belongsTo(OrdemDeProducao::class, 'ordem_de_producao_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
