<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-o-map" collapsible>
        <x-slot name="heading">
            Mapa de Visitas
        </x-slot>
        <x-slot name="headerEnd">

        </x-slot>
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                @livewire(\App\Livewire\MapaDeVisitas::class)
            </div>
            <div>
                <h3>Visitas Agendadas para próximos 7 dias</h3>
                <table>
                    <tr>
                        <th>a</th>
                    </tr>
                </table>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
