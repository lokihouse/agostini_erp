<?php

namespace App\Models;

use App\Models\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([EmpresaScope::class])]
class Cliente extends Model
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
        'recorrencia_de_visitas_dias',
        'localizacao',
    ];

    protected $appends = [
        'endereco_completo',
        'localizacao',
        'proxima_visita',
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

    public function getProximaVisitaAttribute(): ?string
    {
        return Visita::query()
            ->where('cliente_id', $this->id)
            ->where('status', 'agendada')
            ->orderBy('data', 'asc')
            ->first()->data ?? null;
    }

    public function setLocalizacaoAttribute(?array $location): void
    {
        if (is_array($location))
        {
            $this->attributes['latitude'] = $location['lat'];
            $this->attributes['longitude'] = $location['lng'];
            unset($this->attributes['localizacao']);
        }
    }

    public static function getLatLngAttributes(): array
    {
        return [
            'lat' => 'latitude',
            'lng' => 'longitude',
        ];
    }

    public static function getComputedLocation(): string
    {
        return 'localizacao';
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
