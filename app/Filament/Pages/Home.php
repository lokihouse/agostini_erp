<?php

namespace App\Filament\Pages;

use App\Models\Visita;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class Home extends Page
{
    protected static ?string $navigationLabel = 'Início';
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected ?string $heading = '';
    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.home';
}
