<?php

namespace App\Models;

use App\Models\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([EmpresaScope::class])]
class Departamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'descricao',
        'empresa_id',
    ];
}
