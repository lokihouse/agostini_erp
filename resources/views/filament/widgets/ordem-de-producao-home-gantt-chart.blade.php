<x-filament-widgets::widget>
    <x-filament::section compact style="height: 480px">
        <x-slot name="heading">
            Planejamento de Produção
        </x-slot>
        @livewire('high-chart-gantt', [
            'series' => $this->ordens
        ])
    </x-filament::section>
</x-filament-widgets::widget>
