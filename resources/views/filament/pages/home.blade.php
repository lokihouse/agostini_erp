<x-filament-panels::page>
    @can('widget_Producao', Auth::user())
        @livewire(\App\Filament\Widgets\Producao::class)
    @endcan

    @can('widget_Financeiro', Auth::user())
        @livewire(\App\Filament\Widgets\Financeiro::class)
    @endcan

    @can('widget_Visitas', Auth::user())
        @livewire(\App\Filament\Widgets\Visitas::class)
    @endcan

    @can('widget_Cargas', Auth::user())
        @livewire(\App\Filament\Widgets\Cargas::class)
    @endcan

    @can('widget_RecursosHumanos', Auth::user())
        @livewire(\App\Filament\Widgets\RecursosHumanos::class)
    @endcan

</x-filament-panels::page>
