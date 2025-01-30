<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class EmpresaIndex extends Page
{
    use HasPageShield;
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'Cadastros :: Empresa';
    protected static ?string $navigationGroup = 'Cadastro';
    protected static string $view = 'filament.pages.empresa-index';
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 10;
}
