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
        'equipamento_id_origem',
        'insumos',
        'equipamento_id_destino',
        'producao',
        'tempo_producao',
    ];

    protected $appends = [
        'equipamento_id_origem_nome',
        'equipamento_id_destino_nome',
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

    public function getEquipamentoIdOrigemNomeAttribute()
    {
        return $this->equipamento_origem->nome ?? '-';
    }

    public function getEquipamentoIdDestinoNomeAttribute()
    {
        return $this->equipamento_destino->nome ?? '-';
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function equipamento_origem()
    {
        return $this->belongsTo(Equipamento::class, 'equipamento_id_origem');
    }

    public function equipamento_destino()
    {
        return $this->belongsTo(Equipamento::class, 'equipamento_id_destino');
    }
}
