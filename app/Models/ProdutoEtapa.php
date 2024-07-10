<?php

namespace App\Models;

use App\Http\Controllers\ProdutoController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutoEtapa extends Model
{
    use HasFactory;

    protected $fillable = [
        'produto_id',
        'departamento_id_origem',
        'insumos',
        'departamento_id_destino',
        'producao',
        'tempo_producao',
    ];

    protected $appends = [
        'departamento_id_origem_nome',
        'departamento_id_destino_nome',
    ];

    public static function boot()
    {
        parent::boot();
        self::created(function($model){
            ProdutoController::generateMapaDeProducao($model->produto);
        });

        self::updated(function($model){
            ProdutoController::generateMapaDeProducao($model->produto);
        });

        self::deleted(function($model){
            ProdutoController::generateMapaDeProducao($model->produto);
        });
    }

    public function getDepartamentoIdOrigemNomeAttribute()
    {
        return $this->departamento_origem->nome ?? '-';
    }

    public function getDepartamentoIdDestinoNomeAttribute()
    {
        return $this->departamento_destino->nome ?? '-';
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function departamento_origem()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id_origem');
    }

    public function departamento_destino()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id_destino');
    }
}
