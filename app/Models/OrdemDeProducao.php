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

    protected $fillable = [
        'empresa_id',
        'status',
        'data_inicio_agendamento',
        'data_final_agendamento',
        'data_inicio_producao',
        'data_final_producao',
        'data_cancelamento',
        'motivo_cancelamento',
        'mapa_de_processo',
        'produtos',
        'etapas',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
