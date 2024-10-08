<?php

namespace App\Filament\Clusters;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Clusters\Cluster;

class Producao extends Cluster
{
    // use HasPageShield;
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationLabel = 'Produção';
    protected static ?string $clusterBreadcrumb = 'Produção';
}
