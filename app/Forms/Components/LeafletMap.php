<?php

namespace App\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Illuminate\Database\Eloquent\Model;

class LeafletMap extends Field
{
    protected string $view = 'forms.components.leaflet-map';

    public function zoomIn()
    {
        dd("OPA");
    }
}
