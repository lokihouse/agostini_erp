<?php

namespace App\Models;

use App\Http\Controllers\ProdutoController;
use App\Models\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([EmpresaScope::class])]
class ProdutoEtapa extends Model
{
    use HasFactory;

    protected static function setTempoDeProducao($produto_etapa)
    {
        $tempos = array_reduce($produto_etapa->produto->produto_etapas->toArray(), fn ($carry, $item) => $carry + $item['tempo'], 0);
        $produto_etapa->produto->update(['tempo_de_producao' => $tempos]);
    }
    protected static function boot()
    {
        parent::boot();
        static::created(function ($produto_etapa) {
            self::setTempoDeProducao($produto_etapa);
            ProdutoController::updateMapaDeProducaoWithGraphviz($produto_etapa->produto, 'created');
        });
        static::updated(function ($produto_etapa) {
            self::setTempoDeProducao($produto_etapa);
            ProdutoController::updateMapaDeProducaoWithGraphviz($produto_etapa->produto, 'updated');
        });
        static::deleted(function ($produto_etapa) {
            self::setTempoDeProducao($produto_etapa);
            ProdutoController::updateMapaDeProducaoWithGraphviz($produto_etapa->produto, 'deleted');
        });
    }

    protected $fillable = [
        'empresa_id',
        'produto_id',
        'departamento_origem_id',
        'equipamento_origem_id',
        'departamento_destino_id',
        'equipamento_destino_id',
        'producao',
        'tempo'
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function departamento_origem()
    {
        return $this->belongsTo(Departamento::class, 'departamento_origem_id');
    }

    public function equipamento_origem()
    {
        return $this->belongsTo(Equipamento::class, 'equipamento_origem_id');
    }

    public function departamento_destino()
    {
        return $this->belongsTo(Departamento::class, 'departamento_destino_id');
    }

    public function equipamento_destino()
    {
        return $this->belongsTo(Equipamento::class, 'equipamento_destino_id');
    }
}
