<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Login;
use App\Pages\HealthCheck;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Rupadana\ApiService\ApiServicePlugin;
use ShuvroRoy\FilamentSpatieLaravelHealth\FilamentSpatieLaravelHealthPlugin;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        FilamentAsset::register([
            Css::make('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'),
            Js::make('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'),

            // Js::make('highcharts-core', 'https://code.highcharts.com/highcharts.js'),
            Js::make('highcharts-gantt', 'https://code.highcharts.com/gantt/highcharts-gantt.js'),
            // Js::make('highcharts-exporting', 'https://code.highcharts.com/gantt/modules/exporting.js'),
            // Js::make('highcharts-export-data', 'https://code.highcharts.com/modules/export-data.js'),
            Js::make('highcharts-accessibility', 'https://code.highcharts.com/gantt/modules/accessibility.js'),
            Js::make('highcharts-ptBR', url('storage/highchart-pt-br.js')),
        ]);

        return $panel
            ->default()
            ->id('app')
            ->path('')
            ->spa()
            ->login(Login::class)
            ->colors([
                'primary' => Color::Stone,
            ])
            ->font('Inter', provider: GoogleFontProvider::class)
            ->plugins([
                FilamentShieldPlugin::make()
                    ->gridColumns([ 'default' => 1, ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([ 'default' => 3 ]),
                ApiServicePlugin::make(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->topNavigation()
            ->maxContentWidth(MaxWidth::Full)
            ->databaseNotifications()
            ;
    }
}
