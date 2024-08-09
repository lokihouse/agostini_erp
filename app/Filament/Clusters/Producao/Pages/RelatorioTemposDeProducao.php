<?php

namespace App\Filament\Clusters\Producao\Pages;

use App\Filament\Clusters\Producao;
use Filament\Pages\Page;

class RelatorioTemposDeProducao extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.clusters.producao.pages.relatorio-tempos-de-producao';
    protected static ?string $cluster = Producao::class;
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Tempos de Produção';
    protected static ?string $navigationGroup = 'Relatórios';
}
