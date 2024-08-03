<?php

namespace App\Filament\Clusters;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Clusters\Cluster;

class Cadastros extends Cluster
{
    //use HasPageShield;
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
}
