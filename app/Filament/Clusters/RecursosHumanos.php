<?php

namespace App\Filament\Clusters;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Clusters\Cluster;

class RecursosHumanos extends Cluster
{
    // use HasPageShield;
    protected static ?int $navigationSort = 6;
    protected static ?string $navigationIcon = 'heroicon-o-identification';
}
