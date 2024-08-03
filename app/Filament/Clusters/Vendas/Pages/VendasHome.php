<?php

namespace App\Filament\Clusters\Vendas\Pages;

use App\Filament\Clusters\Vendas;
use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget;

class VendasHome extends Page
{
    protected static ?string $navigationLabel = 'Início';
    protected ?string $heading = 'Dashboard de Vendas';
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.clusters.vendas.pages.vendas-home';
    protected static ?string $cluster = Vendas::class;

}
