<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdemDeProducao extends Model
{
    use HasFactory;

    protected $table = 'ordens_de_producao';

    protected $appends = ['codigo'];

    public function getCodigoAttribute()
    {
        return strtoupper(
            str_pad(dechex($this->empresa_id), 4, '0', STR_PAD_LEFT) . '.' .
            str_pad(dechex($this->id), 4, '0', STR_PAD_LEFT)
        );
    }
}
