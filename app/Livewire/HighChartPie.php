<?php

namespace App\Livewire;

use Livewire\Component;

class HighChartPie extends Component
{

    public array $series = [];
    public function render()
    {
        return view('livewire.high-chart-pie');
    }
}
