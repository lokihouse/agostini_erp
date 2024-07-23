<?php

namespace App\Models;

use App\Http\Controllers\ProdutoEtapaController;
use App\Models\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
#[ScopedBy([EmpresaScope::class])]
class Produto extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'descricao', 'valor_unitario', 'tempo_producao', 'empresa_id'];
    protected $with = ['etapas'];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function etapas()
    {
        return $this->hasMany(ProdutoEtapa::class);
    }
}
