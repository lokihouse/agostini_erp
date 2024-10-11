@php
    $ordensEmProducao = \App\Models\OrdemDeProducao::where('status', 'em_producao')->get();
    $ordensAgendadas = \App\Models\OrdemDeProducao::where('status', 'agendada')->where('data_inicio_agendamento', '<=', today())->get();
@endphp

<div class="md:max-w-sm mx-auto mt-4 space-y-4">
    @if(($ordensEmProducao->count() + $ordensAgendadas->count()) <= 0)
        <div class="bg-white rounded-lg shadow p-4 text-lg font-medium text-center">
            Não há ordens de produção em andamento ou agendadas.
        </div>
    @else
        <x-filament::button icon="fas-camera" class="w-full" wire:click="toggleCamera">
            {{ !$cameraAberta ? 'Abrir' : 'Fechar'  }} Câmera
        </x-filament::button>

        @if($cameraAberta)
            <x-barcode-scan></x-barcode-scan>
        @endif

        <x-filament::section compact collapsible collapsed>
            <x-slot name="heading">
                Ordens em Produção
            </x-slot>

            {{-- Content --}}
        </x-filament::section>

        {{--<x-filament::section compact collapsible>
            <x-slot name="heading">
                Ordens Agendadas
            </x-slot>

            <x-slot name="headerEnd">
                <x-filament::badge>
                    {{ $ordensAgendadas->count() }}
                </x-filament::badge>
            </x-slot>

            <div class="space-y-2">
                @foreach($ordensAgendadas as $ordemAgendada)
                    <x-filament::button size="xl"  class="w-full" wire:click="openModal({{ $ordemAgendada->id }})">
                        Ordem de Produção #{{ $ordemAgendada->id }}
                        <br/>
                        <span class="text-xs font-light">{{ Carbon\Carbon::parse($ordemAgendada->data_inicio_agendamento)->format('d/m/y') }} à {{ Carbon\Carbon::parse($ordemAgendada->data_final_agendamento)->format('d/m/y') }}</span>
                    </x-filament::button>
                    <x-filament::modal id="modal_op_{{ $ordemAgendada->id }}" width="xs" icon="heroicon-o-information-circle"  alignment="center">
                        <x-slot name="heading">
                            Iniciar Ordem de Produção
                        </x-slot>

                        <x-slot name="description">
                            Ao confirmar a ordem de produção, a mesma será iniciada.
                        </x-slot>

                        <x-slot name="footer">
                            <div class="grid grid-cols-2 gap-y-2">
                                <x-filament::button class="w-full" wire:click="iniciarProducao({{ $ordemAgendada->id }})">
                                    Inciair
                                </x-filament::button>
                                <x-filament::button color="gray" class="w-full" wire:click="closeModal({{ $ordemAgendada->id }})">
                                    Cancelar
                                </x-filament::button>
                            </div>
                        </x-slot>
                    </x-filament::modal>
                @endforeach
            </div>
        </x-filament::section>--}}

    @endif
</div>
