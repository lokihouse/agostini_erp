<x-filament-panels::page>
    <div class="'p-4 border border-gray-600 bg-gray-200 text-gray-800 text-center">
        <div class="font-bold text-xl p-2">Pedido de Venda # {{ $this->record->id }}</div>
        <div class="font-medium text-sm p-2">{{ $this->record->cliente->razao_social }}<br/>
            ({{ \App\Utils\Cnpj::format($this->record->cliente->cnpj) }})
        </div>
    </div>

    <div class="grid grid-cols-2 gap-2">
        {{ $this->adicionarProdutoAction() }}
        {{ $this->finalizarPedidoAction() }}
    </div>

    <div>
        <div class="relative overflow-x-auto rounded-xl">
            <table class="w-full text-xs">
                <thead class="uppercase">
                    <tr class="bg-gray-200 rounded">
                        <th scope="col" class="w-1"></th>
                        <th scope="col" class="p-2 text-left">
                            Produto
                        </th>
                        <th scope="col" class="p-2 text-center w-1">
                            R$ Un.
                        </th>
                        <th scope="col" class="p-2 text-center w-1">
                            %
                        </th>
                        <th scope="col" class="p-2 text-center w-1">
                            R$ Tot.
                        </th>
                    </tr>
                </thead>
                <tbody class="">
                    @php
                        $total = 0;
                    @endphp
                    @foreach(json_decode($this->record->produtos) ?? [] as $id => $produto)
                        @php
                            $prd = \App\Models\Produto::query()->find($produto->produto_id);
                            $valor = intval($produto->quantidade) * $produto->valor_original * ((100 - intval($produto->desconto)) / 100);
                            $total += $valor;
                            // dd($produto, $valor, $total);
                        @endphp
                    <tr class="">
                        <td class="flex justify-center items-center p-2">
                            <x-filament::icon-button
                                icon="heroicon-o-trash"
                                color="danger"
                                size="xs"
                                wire:click="removeById('{{$id}}')"
                                label="New label"
                            />
                        </td>
                        <td class="p-2 border-s">
                            {{ $produto->quantidade }}x {{ $prd->nome }}
                        </td>
                        <td class="p-2 border-s text-right">
                            {{ \Illuminate\Support\Number::currency($produto->valor_original, 'BRL') }}
                        </td>
                        <td class="p-2 border-s text-right">
                            {{ $produto->desconto }}
                        </td>
                        <td class="p-2 border-s text-right">
                            {{ \Illuminate\Support\Number::currency($valor, 'BRL') }}
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
                            {{ \Illuminate\Support\Number::currency($total, 'BRL') }}
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>
{{--    <pre>{{ json_encode($this->record,128) }}</pre>--}}
</x-filament-panels::page>
