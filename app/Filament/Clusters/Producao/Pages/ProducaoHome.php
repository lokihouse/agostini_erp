<?php

namespace App\Filament\Clusters\Producao\Pages;

use App\Filament\Clusters\Producao;
use Filament\Pages\Page;

class ProducaoHome extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.clusters.producao.pages.producao-home';

    protected static ?string $cluster = Producao::class;
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Início';
    protected ?string $heading = 'Dashboard de Produção';
}
