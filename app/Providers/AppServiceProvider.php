<?php

namespace App\Providers;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::unguard();
        // URL::forceScheme('https');

        /*FilamentAsset::register([
            Css::make('leaflet_css', asset('css/leaflet.css')),
            Js::make('leaflet_js', asset('js/leaflet.js')),
        ]);*/
    }
}
