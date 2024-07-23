<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroDePonto extends Model
{
    use HasFactory;

    protected $table = 'registros_de_ponto';

    public function funcionario()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
