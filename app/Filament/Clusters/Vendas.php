<?php

namespace App\Filament\Clusters;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Clusters\Cluster;

class Vendas extends Cluster
{
    // use HasPageShield;
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
}
