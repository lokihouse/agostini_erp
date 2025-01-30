<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioDeTrabalho extends ModelBase
{
    protected $table = 'horarios_de_trabalho';
    public function jornada_de_trabalho(): BelongsTo
    {
        return $this->belongsTo(JornadaDeTrabalho::class);
    }
}
