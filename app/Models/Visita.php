<?php

namespace App\Models;

use App\Models\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([EmpresaScope::class])]
class Visita extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'user_id',
        'status',
        'data',
        'motivo',
        'observacao_cancelamento',
        'hora_inicial',
        'observacao_inicial',
        'imagem_inicial',
        'hora_final',
        'observacao_final',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'id');
    }

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
