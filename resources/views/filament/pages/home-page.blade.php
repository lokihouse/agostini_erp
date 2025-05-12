<x-filament-panels::page>
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-2">
        <div>
            @livewire('time-clock.time-clock-manager')
        </div>
        <div>
            @livewire('user-task-control')
        </div>
        <div>
            @livewire('scheduled-visits-map')
        </div>
    </div>
</x-filament-panels::page>
