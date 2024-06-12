<x-filament-panels::page>

    @can('widget_Visitas', Auth::user())
        @livewire(\App\Filament\Widgets\Visitas::class)
    @endcan

</x-filament-panels::page>
