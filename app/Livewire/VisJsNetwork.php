<?php

namespace App\Livewire;

use Livewire\Component;

class VisJsNetwork extends Component
{
    public $nodes;
    public $edges;
    public $id;

    public function mount($nodes = [], $edges = [])
    {
        $this->nodes = $nodes;
        $this->edges = $edges;
        $this->id = rand();
    }

    public function render()
    {
        return view('livewire.vis-js-network');
    }
}
