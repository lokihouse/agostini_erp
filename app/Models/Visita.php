<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visita extends ModelBase
{
    protected $appends = ['produtos_no_pedido_de_venda'];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, );
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getProdutosNoPedidoDeVendaAttribute() {
        return PedidoDeVenda::query()->where('id', $this->pedido_de_venda_id)->first()->produtos ?? [];
    }
}
