<?php

namespace App\Filament\Clusters;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Clusters\Cluster;

class Financeiro extends Cluster
{
    //use HasPageShield;
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
}
