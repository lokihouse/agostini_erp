<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-o-map">
        <x-slot name="heading">
            Mapa de Visitas
        </x-slot>
        <x-slot name="headerEnd">
            <div class="hidden md:block">
                <x-filament::button color="danger" size="xs" wire:click="$set('activeTab', 'tab1')">
                    Atrasadas
                </x-filament::button>

                <x-filament::button size="xs" wire:click="$set('activeTab', 'tab2')">
                    15 dias
                </x-filament::button>

                <x-filament::button color="gray" size="xs" href="{{ route('filament.app.vendas.resources.visitas.index') }}" tag="a">
                    Ver todas
                </x-filament::button>
            </div>
        </x-slot>
        @if($activeTab === 'tab1')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                @livewire(\App\Livewire\VisitasAtrasadasMapa::class)
            </div>
            <div class="md:col-span-2">
                @livewire(\App\Livewire\VisitasAtrasadas::class)
            </div>
        </div>
        @endif
        @if($activeTab === 'tab2')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    @livewire(\App\Livewire\VisitasDiasMapa::class, ['dias' => 15])
                </div>
                <div class="md:col-span-2">
                    @livewire(\App\Livewire\VisitasDias::class, ['dias' => 15])
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
