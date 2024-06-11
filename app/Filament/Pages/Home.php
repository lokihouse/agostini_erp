<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class Home extends Page
{
    protected static ?string $navigationLabel = 'Início';
    protected ?string $heading = '';
    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.home';
}
