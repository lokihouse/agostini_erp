<?php

namespace App\Filament\Clusters;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Clusters\Cluster;

class Cargas extends Cluster
{
    // use HasPageShield;
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
}
