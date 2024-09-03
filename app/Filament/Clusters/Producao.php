<?php

namespace App\Filament\Clusters;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Clusters\Cluster;

class Producao extends Cluster
{
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'fas-diagram-project';
}
