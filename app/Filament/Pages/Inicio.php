<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Inicio extends Page
{
    protected static ?string $title = 'Início';
    protected ?string $heading = '';
    protected static string $view = 'filament.pages.inicio';
    protected static ?int $navigationSort = -1;
}
