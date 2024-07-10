<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\RecursosHumanos;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class RDPR extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $cluster = RecursosHumanos::class;
    protected static string $view = 'report.registro_de_ponto';

    protected function getViewData(): array
    {

        return [
            'origin' => false,
            'intervalo' => '01/01/2024 - 31/12/2024',
        ];
    }
}
