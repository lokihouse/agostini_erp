<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProdutoEtapaOrigem extends ModelBase
{
    protected $table = "produto_etapas_origens";
    public function produto_etapa(): BelongsTo
    {
        return $this->belongsTo(ProdutoEtapa::class);
    }
}
