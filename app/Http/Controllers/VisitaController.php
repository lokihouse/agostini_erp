<?php

namespace App\Http\Controllers;

use App\Models\Visita;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VisitaController extends Controller
{
    public function atrasadas()
    {
        return Visita::query()
            ->with('cliente')
            ->where('status', 'agendada')
            ->where('data', '<=', Carbon::make('today')->format('Y-m-d'));
    }

    public function proximosDias($dias = 15)
    {
        return Visita::query()
            ->with('cliente')
            ->where('status', 'agendada')
            ->where('data', '>', Carbon::make('today')->format('Y-m-d'))
            ->where('data', '<=', Carbon::make('today')->addDays($dias)->format('Y-m-d'));
    }
}
