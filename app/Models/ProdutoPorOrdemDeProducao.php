<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdutoPorOrdemDeProducao extends Model
{
    protected $table = 'produtos_por_ordem_de_producao';

    protected static function booted(): void
    {
        self::created(static function (ProdutoPorOrdemDeProducao $produtoPorOrdemDeProducao): void {
            sleep(1);
            $eventoPorOrdemDeProducao = new EventosPorOrdemDeProducao();
            $eventoPorOrdemDeProducao->ordem_de_producao_id = $produtoPorOrdemDeProducao->ordem_de_producao_id;
            $eventoPorOrdemDeProducao->user_id = auth()->user()->id;
            $eventoPorOrdemDeProducao->nome = "Adição de " . $produtoPorOrdemDeProducao->quantidade . "x de "
                . $produtoPorOrdemDeProducao->produto->nome;
            $eventoPorOrdemDeProducao->save();
        });

        self::deleted(static function (ProdutoPorOrdemDeProducao $produtoPorOrdemDeProducao): void {
            sleep(1);
            $eventoPorOrdemDeProducao = new EventosPorOrdemDeProducao();
            $eventoPorOrdemDeProducao->ordem_de_producao_id = $produtoPorOrdemDeProducao->ordem_de_producao_id;
            $eventoPorOrdemDeProducao->user_id = auth()->user()->id;
            $eventoPorOrdemDeProducao->nome = "Remoção de " . $produtoPorOrdemDeProducao->quantidade . "x de "
                . $produtoPorOrdemDeProducao->produto->nome;
            $eventoPorOrdemDeProducao->save();
        });
    }

    public function ordem_de_producao(): BelongsTo
    {
        return $this->belongsTo(OrdemDeProducao::class, 'ordem_de_producao_id');
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}
