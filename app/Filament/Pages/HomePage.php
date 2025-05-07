<?php

namespace App\Filament\Pages;

// --- IMPORTS NECESSÁRIOS ---
use App\Filament\Widgets\UserTaskWidget; // Garanta que este import existe
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Log; // Garanta que este import existe
// --- FIM IMPORTS ---

class HomePage extends Page
{
    protected static string $view = 'filament.pages.home-page';
    protected static ?string $title = 'Início';

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
}
