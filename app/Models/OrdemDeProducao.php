<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrdemDeProducao extends Model
{
    protected $table = 'ordens_de_producao';

    protected static function booted(): void
    {
        self::created(static function (OrdemDeProducao $ordemDeProducao): void {
            $eventoPorOrdemDeProducao = new EventosPorOrdemDeProducao();
            $eventoPorOrdemDeProducao->ordem_de_producao_id = $ordemDeProducao->id;
            $eventoPorOrdemDeProducao->user_id = auth()->user()->id;
            $eventoPorOrdemDeProducao->nome = "Criação da ordem de produção";
            $eventoPorOrdemDeProducao->save();
        });
    }
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function produtos(): HasMany
    {
        return $this->hasMany(ProdutoPorOrdemDeProducao::class, 'ordem_de_producao_id');
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(EventosPorOrdemDeProducao::class, 'ordem_de_producao_id');
    }
}
