<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AverageProductionTimes;
use App\Filament\Widgets\PauseTimesOverview;
use App\Filament\Widgets\ProductionStatsOverview;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

// use Filament\Pages\Dashboard as BaseDashboard; // Mantenha como BaseDashboard

class DashboardProduction extends \Filament\Pages\Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static string $view = 'filament.pages.dashboard-production';
    protected static ?string $navigationGroup = 'Produção'; // Grupo onde vai aparecer
    protected static ?string $navigationLabel = 'Dashboard'; // Nome no menu
    protected static ?int $navigationSort = 30; // Ordem dentro do grupo (0 = primeiro)
    protected static ?string $title = 'Dashboard de Produção'; // Título da página
    // --- Fim das propriedades adicionadas ---


    // O método getWidgets() permanece o mesmo
    public function getWidgets(): array
    {
        return [
            ProductionStatsOverview::class,
            PauseTimesOverview::class,
            AverageProductionTimes::class,
        ];
    }

    // IMPORTANTE: Remova ou comente o método getNavigationItems() se ele existir,
    // pois as propriedades estáticas acima cuidam da navegação.
    // public static function getNavigationItems(): array { ... } // REMOVA OU COMENTE

    // Se você precisar que esta página NÃO seja a rota padrão do painel,
    // adicione esta propriedade (embora definir outra página em ->pages() já faça isso)
    protected static bool $isDiscovered = true; // Garante que seja descoberta
    protected static ?string $slug = 'dashboard-producao'; // Define um slug específico
    // protected static bool $shouldRegisterNavigation = true; // Garante que apareça na navegação

}
