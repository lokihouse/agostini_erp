<?php

namespace App\Models;

use App\Http\Controllers\ProdutoController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdemDeProducaoProduto extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();
        self::created(function (OrdemDeProducaoProduto $ordemDeProducaoProduto) {
        });
    }

    protected $fillable = [
        'ordem_de_producao_id',
        'produto_id',
        'quantidade',
    ];

    protected $with = ['produto'];

    public function ordem()
    {
        return $this->belongsTo(OrdemDeProducao::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
