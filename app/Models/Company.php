<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Atributos que podem ser atribuídos em massa.
     */
    protected $fillable = [
        'name',
        'socialName',
        'taxNumber',
        'address_zip_code',
        'address_street',
        'address_number',
        'address_complement',
        'address_district',
        'address_city',
        'address_state',
        'latitude',
        'longitude',
        'telephone',
    ];

    /**
     * Conversões de tipo para atributos.
     */
    protected $casts = [
        // Nenhum cast necessário por enquanto
    ];

    /**
     * Relacionamento: Uma Empresa tem muitos Usuários.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'company_id', 'uuid');
    }

    public function workShifts(): HasMany
    {
        return $this->hasMany(WorkShift::class, 'company_id', 'uuid');
    }

    public function holidays(): HasMany
    {
        return $this->hasMany(Holiday::class, 'company_id', 'uuid');
    }

    // Em App\Models\Company.php e App\Models\Client.php
    public function getFullAddress(): string
    {
        return trim(implode(', ', array_filter([
            $this->address_street,
            $this->address_number,
            $this->address_complement,
            $this->address_neighborhood,
            $this->address_city,
            $this->address_state,
            $this->address_zip_code,
        ])));
    }
}
