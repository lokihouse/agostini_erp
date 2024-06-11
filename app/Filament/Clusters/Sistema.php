<?php

namespace App\Filament\Clusters;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Clusters\Cluster;

class Sistema extends Cluster
{
    use HasPageShield;

    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
}
