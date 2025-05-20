<x-filament-panels::page>
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-2">
        <div>
            @livewire('time-clock.time-clock-manager')
        </div>
        @if(auth()->user() && (auth()->user()->hasRole('Produção') || auth()->user()->hasRole(config('filament-shield.super_admin.name'))))
            <div>
                @livewire('user-task-control')
            </div>
        @endif
        @if(auth()->user() && (auth()->user()->hasRole('Vendedor') || auth()->user()->hasRole(config('filament-shield.super_admin.name'))))
            <div>
                @livewire('scheduled-visits-map')
            </div>
        @endif
        @if(auth()->user() && (auth()->user()->hasRole('Motorista') || auth()->user()->hasRole(config('filament-shield.super_admin.name'))))
            <div>
                @livewire('driver-delivery-manager')
            </div>
        @endif
    </div>
</x-filament-panels::page>
