<?php

namespace App\Filament\Widgets;

use App\Models\Visita;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class Cargas extends Widget
{
    use HasWidgetShield;
    protected static string $view = 'filament.widgets.cargas';
}
