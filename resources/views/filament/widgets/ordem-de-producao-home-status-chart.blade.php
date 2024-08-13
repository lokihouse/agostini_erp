<x-filament-widgets::widget>
    <x-filament::section compact style="height: 480px">
        <x-slot name="heading">
            Ordens por Status
        </x-slot>
        @livewire('high-chart-pie', [
            'series' => $this->ordens
        ])
    </x-filament::section>
</x-filament-widgets::widget>
