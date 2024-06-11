<?php

namespace App\Models;

use App\Models\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// #[ScopedBy([EmpresaScope::class])]
class Empresa extends Model
{
    use HasFactory;

    protected $fillable = ['nome'];

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function produtos()
    {
        return $this->hasMany(Produto::class);
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }

    public function visitas()
    {
        return $this->hasMany(Visita::class);
    }
}
