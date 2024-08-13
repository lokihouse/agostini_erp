<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class OrdemDeProducaoHomeGanttChart extends Widget
{
    protected static string $view = 'filament.widgets.ordem-de-producao-home-gantt-chart';
    public array $ordens = [];
}
