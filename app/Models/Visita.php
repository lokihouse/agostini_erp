<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visita extends ModelBase
{

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, );
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pedido_de_venda(): HasOne
    {
        return $this->hasOne(PedidoDeVenda::class);
    }
}
