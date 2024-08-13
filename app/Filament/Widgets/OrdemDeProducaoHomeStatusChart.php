<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class OrdemDeProducaoHomeStatusChart extends Widget
{
    protected static string $view = 'filament.widgets.ordem-de-producao-home-status-chart';
    public array $ordens = [];
}
