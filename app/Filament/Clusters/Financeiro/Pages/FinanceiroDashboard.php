<?php

namespace App\Filament\Clusters\Financeiro\Pages;

use App\Filament\Clusters\Financeiro;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class FinanceiroDashboard extends Page
{
    // use HasPageShield;
    protected ?string $heading = 'Dashboard';
    protected static ?string $title = 'Financeiro - Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?int $navigationSort = 999;
    protected static ?string $navigationIcon = 'fas-mobile';
    protected static string $view = 'filament.clusters.financeiro.pages.financeiro-dashboard';
    protected static ?string $cluster = Financeiro::class;
}
