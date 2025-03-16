<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Number;

class PedidoDeVenda extends ModelBase
{
    protected $appends = ["valor_de_produtos"];

    public function getValorDeProdutosAttribute(){
        $produtos = json_decode($this->produtos, true);
        $total = 0;
        foreach ($produtos as $produto){
            $total += $produto['subtotal'];
        }
        return Number::format($total, 2);
    }
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    public function produtos()
    {
        return $this->hasMany(ProdutosPorPedidoDeVenda::class, 'pedido_de_venda_id')->withTrashed();
    }
}
