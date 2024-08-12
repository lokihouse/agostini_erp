<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'cnpj',
        'razao_social',
        'nome_fantasia',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'municipio',
        'uf',
        'cep',
        'email',
        'telefone',
        'latitude',
        'longitude',
        'horarios',
        'tolerancia_turno',
        'tolerancia_jornada',
        'raio_cerca',
        'justificativa_dias',
    ];

    protected $appends = [
        'endereco_completo',
        'localizacao',
    ];

    public function getLocalizacaoAttribute(): array
    {
        return [
            "lat" => (float)$this->latitude,
            "lng" => (float)$this->longitude,
        ];
    }

    public function getEnderecoCompletoAttribute(): string
    {
        return "{$this->logradouro}, {$this->numero} - {$this->bairro}, {$this->municipio} - {$this->uf}, {$this->cep}";
    }

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function eventos()
    {
        return $this->hasMany(Evento::class);
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
