<?php

namespace App\Filament\Clusters\Producao\Pages;

use App\Filament\Clusters\Producao;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class ProducaoCronograma extends Page
{
    use HasPageShield;
    protected ?string $heading = 'Cronograma';
    protected static ?string $title = 'Produção - Cronograma';
    protected static ?string $navigationLabel = 'Cronograma';
    protected static ?string $navigationIcon = 'fas-calendar-days';
    protected static string $view = 'filament.clusters.producao.pages.producao-cronograma';
    protected static ?string $cluster = Producao::class;
    protected static ?int $navigationSort = 1;
}
