<?php

namespace App\Models;

use App\Models\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([EmpresaScope::class])]
class OrdemDeProducao extends Model
{
    use HasFactory;

    protected $table = 'ordens_de_producao';

    protected $with = ['produtos_na_ordem', 'etapas_na_ordem'];

    protected $fillable = [
        'empresa_id',
        'status',
        'data_inicio_agendamento',
        'data_final_agendamento',
        'data_inicio_producao',
        'data_final_producao',
        'data_cancelamento',
        'motivo_cancelamento'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function produtos_na_ordem(): HasMany
    {
        return $this->hasMany(OrdemDeProducaoProduto::class);
    }

    public function etapas_na_ordem(): HasMany
    {
        return $this->hasMany(OrdemDeProducaoProdutoEtapa::class);
    }
}
