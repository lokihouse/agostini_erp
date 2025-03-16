<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdutosPorPedidoDeVenda extends ModelBase
{
    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }
}
