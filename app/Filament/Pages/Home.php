<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Home extends Page
{
    protected static ?string $navigationLabel = 'Início';
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected ?string $heading = '';
    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.home';

    protected static bool $shouldRegisterNavigation = false;
}
