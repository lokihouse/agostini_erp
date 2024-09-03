<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        "cnpj",
        "razao_social",
        "nome_fantasia",
        "cep",
        "logradouro",
        "numero",
        "complemento",
        "bairro",
        "municipio",
        "uf",
        "email",
        "telefone",
        "latitude",
        "longitude",
        "raio_cerca",
    ];

    protected $appends = [
        'cerca_geografica_mapa'
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function departamentos()
    {
        return $this->hasMany(Departamento::class);
    }

    public function equipamentos()
    {
        return $this->hasMany(Equipamento::class);
    }

    public function produtos()
    {
        return $this->hasMany(Produto::class);
    }

    public function getCercaGeograficaMapaAttribute()
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'raio' => $this->raio_cerca
        ];
    }
}
