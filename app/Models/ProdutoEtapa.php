<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProdutoEtapa extends ModelBase
{
    protected $appends = ['tempo_de_producao'];

    public function getTempoDeProducaoAttribute()
    {
        return sprintf('%02d:%02d', $this->tempo_de_producao_segundos / 3600, floor($this->tempo_de_producao_segundos / 60) % 60);
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    public function origens(): HasMany
    {
        return $this->hasMany(ProdutoEtapaOrigem::class);
    }

    public function destinos(): HasMany
    {
        return $this->hasMany(ProdutoEtapaDestino::class);
    }
}
