<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'descricao', 'valor_unitario', 'tempo_producao', 'empresa_id'];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
