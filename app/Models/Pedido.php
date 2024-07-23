<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $with = ['visita'];

    protected $fillable = [
        'visita_id',
        'empresa_id',
        'status',
        'observacao_cancelamento',
        'confirmacao',
        'producao',
        'entrega',
        'observacao_entrega'
    ];

    public function visita()
    {
        return $this->belongsTo(Visita::class);
    }
}
