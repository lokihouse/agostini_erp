<?php

namespace App\Models;

use App\Models\Scopes\EmpresaScope;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([EmpresaScope::class])]
class JornadaDeTrabalho extends ModelBase
{
    protected $table = 'jornadas_de_trabalho';

    protected $appends = ['carga_horaria_acumulada'];

    public function getCargaHorariaAcumuladaAttribute()
    {
        $acumulado = array_reduce($this->horarios_de_trabalho->toArray(), function($older, $newer) {
            if($newer['dia_do_ciclo'] > $this->dias_de_ciclo) return $older;
            return $older + Carbon::parse($newer['entrada'])->diffInSeconds(Carbon::parse($newer['saida']));
        }, 0);
        $intervalo = CarbonInterval::seconds($acumulado)->cascade();
        // dd($intervalo);
        return $intervalo->d * 24 + $intervalo->h . ":" . str_pad($intervalo->i, 2, '0') . ":" . str_pad($intervalo->s, 2, '0');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }
    public function horarios_de_trabalho(): HasMany
    {
        return $this->hasMany(HorarioDeTrabalho::class);
    }
}
