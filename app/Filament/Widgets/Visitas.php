<?php

namespace App\Filament\Widgets;

use App\Models\Visita;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class Visitas extends Widget
{
    use HasWidgetShield;
    protected static string $view = 'filament.widgets.visitas';

    public $activeTab = "tab1";

    protected function getVisitasAtrasadas(): array
    {
        if(!Auth::user()->can('widget_Visitas')) return [];

        $visitas_atrasadas = Visita::query()
            ->with('cliente')
            ->where('status', 'agendada')
            ->where('data', '<=', now()->format('Y-m-d'))
            ->get()
            ->toArray();

        return $visitas_atrasadas;
    }

    protected function getVisitas15Dias(): array
    {
        if(!Auth::user()->can('widget_Visitas')) return [];

        $visitas_atrasadas = Visita::query()
            ->with('cliente')
            ->where('status', 'agendada')
            ->where('data', '>', now()->format('Y-m-d'))
            ->where('data', '<=', Carbon::make('now')->addDays(15)->format('Y-m-d'))
            ->get()
            ->toArray();

        return $visitas_atrasadas;
    }
}
