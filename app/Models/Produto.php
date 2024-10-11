<?php

namespace App\Models;

use App\Models\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([EmpresaScope::class])]
class Produto extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();
    }

    protected $fillable = [
        'empresa_id',
        'nome',
        'descricao',
        'valor_minimo',
        'valor_venda',
        'tempo_de_producao',
        'mapa_de_producao',
        'volumes'
    ];

    protected $appends = [
        'volumes_count'
    ];

    public function getVolumesCountAttribute()
    {
        return count(json_decode($this->volumes)) ?? 0;
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function produto_etapas()
    {
        return $this->hasMany(ProdutoEtapa::class);
    }

    public function ordens_de_producao()
    {
        return $this->belongsToMany(OrdemDeProducao::class, 'ordem_de_producao_produtos')
            ->withPivot('quantidade');
    }
}
