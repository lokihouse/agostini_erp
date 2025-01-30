<x-filament-panels::page>

    <div class="text-center">
        <div class="{{ $this->classeTituloPorStatus() }}">
            <div class="text-2xl font-bold">Registro de Visita #{{ $this->record->id }}</div>
            <div>{{ \Carbon\Carbon::parse($this->record->data)->translatedFormat('d/m/Y') }}</div>
            <div class="text-xs">{{ $this->record->status }}</div>
        </div>
    </div>

    <div class="grid gap-2">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            @if($this->record->status === 'agendada')
                <x-filament::button wire:click="checkInVisita">
                    Realizar Visita
                </x-filament::button>
            @endif

            @if($this->record->status === 'em andamento')
                <x-filament::button color="success" wire:click="goToPedido">
                    Realizar Pedido
                </x-filament::button>
            @endif

            <x-filament::button color="danger" wire:click="mountAction('cancelarVisita')">
                Cancelar Visita
            </x-filament::button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
            <x-filament::section compact collapsible>
                <x-slot name="heading">
                    Dados do Cliente
                </x-slot>

                {{ $this->clienteInfolist }}
            </x-filament::section>

            @if(isset($this->record->pedido_de_venda_id))
                <x-filament::section compact collapsible>
                    <x-slot name="heading">
                        Pedido
                    </x-slot>

                    //
                </x-filament::section>
            @endif

            @if($this->record->status === 'cancelada')
            <x-filament::section compact collapsible>
                <x-slot name="heading">
                    Cancelamento
                </x-slot>

                {{ $this->cancelamentoInfolist }}
            </x-filament::section>
            @endif

        </div>
    </div>

</x-filament-panels::page>
