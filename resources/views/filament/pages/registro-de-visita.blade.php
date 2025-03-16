<x-filament-panels::page>

    <div class="flex flex-col sm:space-x-2 sm:flex-row">
        <div class="flex-1 text-center mb-2 sm:mb-0 {{ $this->classeTituloPorStatus() }}">
            <div class="text-2xl font-bold">Registro de Visita #{{ $this->record->id }}</div>
            <div>{{ \Carbon\Carbon::parse($this->record->data)->translatedFormat('d/m/Y') }}</div>
            <div class="text-xs">{{ $this->record->status }}</div>
        </div>
        @if($this->record->status !== 'finalizada')
            <div class="flex flex-col items-center justify-center space-y-2 w-full sm:max-w-sm">
                @if($this->record->status === 'agendada') {{ $this->checkInVisitaAction() }} @endif
                @if($this->record->status === 'em andamento') {{ $this->adicionarProdutoAction() }} @endif
                @if($this->record->status !== 'finalizada') {{ $this->encerrarVisitaSemPedidoAction() }}@endif
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 -mt-4">
        <div class="col-span-full sm:col-span-1">
            <x-filament::section compact collapsible collapsed>
                <x-slot name="heading">
                    Dados do Cliente
                </x-slot>

                {{ $this->clienteInfolist }}
            </x-filament::section>
        </div>

        @if($this->record->status === 'em andamento')
            <div class="col-span-full sm:col-span-3">
                <x-filament::section compact collapsible>
                    <x-slot name="heading">
                        Pedido
                    </x-slot>

                    <div class="relative overflow-x-auto -mx-4 -mt-4">
                        <table class="w-full text-xs text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr class="bg-gray-100">
                                    <th scope="col" class="p-2 w-1">&nbsp;</th>
                                    <th scope="col" class="border-s">
                                        Produto
                                    </th>
                                    <th scope="col" class="border-s w-1 min-w-[70px]">
                                        Un.
                                    </th>
                                    <th scope="col" class="border-s w-1 min-w-[40px]">
                                        %
                                    </th>
                                    <th scope="col" class="border-s w-1 min-w-[80px]">
                                        R$
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="">
                            @php
                                $total = 0;
                            @endphp
                            @foreach($this->produtos as $id => $produto)
                                @php
                                    $total += $produto['subtotal'];
                                @endphp
                                <tr class="border-b">
                                    <td class="flex justify-center items-center p-2">
                                        <x-filament::icon-button
                                            icon="heroicon-o-trash"
                                            color="danger"
                                            size="xs"
                                            wire:click="removeById('{{$id}}')"
                                        />
                                    </td>
                                    <td class="border-s ps-2">
                                        {{ $produto['quantidade'] }}x {{ $produto['produto_nome'] }}
                                    </td>
                                    <td class="border-s text-center">
                                        @money($produto['valor_original'])
                                    </td>
                                    <td class="border-s text-center">
                                        {{ \Illuminate\Support\Number::format($produto['desconto'], 2) }}
                                    </td>
                                    <td class="border-s text-center">
                                        @money($produto['subtotal'])
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr class="bg-gray-200">
                                <th scope="col" class="p-2 w-1">
                                    Total
                                </th>
                                <th scope="col" colspan="5" class="p-2 text-right w-[80px]">
                                    @money($total)
                                </th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if(!empty($this->produtos))
                    <div class="-mx-4 -mb-4">
                        {{ $this->finalizarPedidoAction() }}
                    </div>
                    @endif
                </x-filament::section>
            </div>
        @endif

        @if($this->record->status === 'finalizada' && $this->record->pedido_de_venda_id)
            <div class="col-span-full sm:col-span-3">
                <x-filament::section compact collapsible>
                    <x-slot name="heading">
                        Pedido
                    </x-slot>
                    <div class="relative overflow-x-auto -m-4">
                        <table class="w-full text-xs rounded-b-xl text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr class="bg-gray-100">
                                <th scope="col" class="border-s w-full py-2">
                                    Produto
                                </th>
                                <th scope="col" class="border-s w-1 min-w-[100px]">
                                    Un.
                                </th>
                                <th scope="col" class="border-s w-1 min-w-[50px]">
                                    %
                                </th>
                                <th scope="col" class="border-s w-1 min-w-[100px]">
                                    R$
                                </th>
                            </tr>
                            </thead>
                            <tbody class="">
                            @php
                                $total = 0;
                            @endphp
                            @foreach($this->record->pedido_de_venda->produtos as $id => $produto)
                                @php
                                    $total += $produto['subtotal'];
                                @endphp
                                <tr class="border-b">
                                    <td class="px-2">
                                        {{ $produto['quantidade'] }}x {{ $produto->produto->nome }}
                                    </td>
                                    <td class="border-s text-right px-2">
                                        @money($produto['valor_original'])
                                    </td>
                                    <td class="border-s text-center px-2">
                                        {{ \Illuminate\Support\Number::format($produto['desconto'], 2) }}
                                    </td>
                                    <td class="border-s text-right px-2">
                                        @money($produto['subtotal'])
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot class="rounded-b-xl">
                            <tr class="bg-gray-200 rounded-b-xl">
                                <th scope="col" class="p-2 w-1 rounded-bl-xl">
                                    Total
                                </th>
                                <th scope="col" colspan="5" class="p-2 text-right w-[80px] rounded-br-xl">
                                    @money($total)
                                </th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </x-filament::section>
            </div>
        @endif

        @if($this->record->status === 'finalizada' && !$this->record->pedido_de_venda_id)
            <div class="col-span-full sm:col-span-3">
                <livewire:registro-de-visita-relatorio :relatorio="$this->record->relatorio_de_visita"/>
            </div>
        @endif
    </div>

</x-filament-panels::page>
