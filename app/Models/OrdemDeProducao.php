<?php

namespace App\Models;

use App\Models\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([EmpresaScope::class])]
class OrdemDeProducao extends Model
{
    use HasFactory;

    protected $table = 'ordens_de_producao';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'status',
        'motivo_cancelamento',
        'previsao_inicio',
        'previsao_final',
        'data_inicio',
        'data_final',
        'produtos',
    ];

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
