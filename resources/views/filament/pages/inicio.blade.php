<x-filament-panels::page>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="w-full">
            @livewire(\App\Filament\Widgets\RegistroDePonto::class)
        </div>
        <div class="w-full sm:col-span-1">
            @livewire(\App\Filament\Widgets\MapaDeVisitas::class)
        </div>
        <div class="w-full sm:col-span-1">
            @livewire(\App\Filament\Widgets\RegistroDeEntregas::class)
        </div>
    </div>
</x-filament-panels::page>
