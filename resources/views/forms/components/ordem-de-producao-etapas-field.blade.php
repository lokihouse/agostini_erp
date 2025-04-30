@php
    $state = $getState();
    $nodes = [
        ['id' => 0, 'shape' => 'circularImage', 'image' => '/images/svg/heroicons.circle-play.svg', 'color' => ['background' => 'white', 'border' => 'transparent'], 'fixed' => true],
        ['id' => -1, 'shape' => 'circularImage', 'image' => '/images/svg/heroicons.circle-stop.svg', 'color' => ['background' => 'white', 'border' => 'transparent']],
    ];
    $edges = [];

    if ($state !== null) {
        foreach ($state as $st) {
            $nodes[] = [
                'id' => $st['id'],
                'label' => $st['nome'],
                'shape' => 'box',
                'color' => [
                    'background' => 'white',
                    'border' => 'black'
                ],
                'margin' => 10
            ];

            foreach ($st['origem'] as $origem) {
                $edges[] = [
                    'from' => $origem === null ? 0 : $origem,
                    'to' => $st['id'],
                    'color' => 'black',
                    'arrows' => [
                        'to' => [
                            'enabled' => true
                        ]
                    ]
                ];
            }

            foreach ($st['destino'] as $destinos) {
                $edges[] = [
                    'from' => $st['id'],
                    'to' => $destinos === null ? -1 : $destinos,
                    'color' => 'black',
                    'arrows' => [
                        'to' => [
                            'enabled' => true
                        ]
                    ]
                ];
            }
        }

        usort($state, fn($a, $b) => strcmp($a['nome'], $b['nome']));
    }else {
        $state = [];
    }
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
    class="-m-4"
>
    <div x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }">
        <div class="rounded-b-xl bg-gray-100 dark:border-gray-700 border-gray-200 px-4">
            @livewire('vis-js-network', ['nodes' => $nodes, 'edges' => $edges])
            <ol class="relative border-s border-gray-200 dark:border-gray-700">
                @foreach($state as $etapa)
                <li class="ms-4">
                    <div class="absolute w-3 h-3 bg-gray-200 rounded-full mt-1.5 -start-1.5 border border-white dark:border-gray-900 dark:bg-gray-700"></div>
                    <div class="flex">
                        <div class="text-lg font-semibold text-gray-900 dark:text-white flex-1">{{$etapa['nome']}}</div>
                        <div class="pe-2 text-sm font-normal leading-none text-gray-400 dark:text-gray-500">{{$etapa['tempo_de_producao']}}</div>
                    </div>
                    @if($etapa['descricao'])
                    <p class="text-xs font-normal text-gray-500 dark:text-gray-400">{{$etapa['descricao']}}</p>
                    @endif
                    <div class="grid grid-cols-2 gap-4 text-xs">
                        @if(!empty(json_decode($etapa['insumos'] ?? '[]', true)))
                        <div>
                            <div class="relative overflow-x-auto">
                                <table class="w-full text-xs text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                    <thead class="text-xs text-gray-700 border uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                                    <tr class="bg-gray-200">
                                        <th scope="col" colspan="2" class="p-2 w-1 text-center">
                                            Insumos
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="col" class="border-e p-2 w-1">
                                            Quant.
                                        </th>
                                        <th scope="col" class="p-2">
                                            Descrição
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="border">
                                    @foreach(json_decode($etapa['insumos'] ?? '[]', true) as $ins)
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                                            <td class="p-2 border-e">{{ $ins['quantidade'] }}</td>
                                            <td class="p-2">{{ $ins['descricao'] }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                        @if(!empty(json_decode($etapa['producao'] ?? '[]', true)))
                        <div>
                            <div class="relative overflow-x-auto">
                                <table class="w-full text-xs text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                    <thead class="text-xs text-gray-700 border uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                                    <tr class="bg-gray-200">
                                        <th scope="col" colspan="2" class="p-2 w-1 text-center">
                                            Produção
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="col" class="border-e p-2 w-1">
                                            Quant.
                                        </th>
                                        <th scope="col" class="p-2">
                                            Descrição
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="border">
                                    @foreach(json_decode($etapa['producao'] ?? '[]', true) as $ins)
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                                            <td class="p-2 border-e">{{ $ins['quantidade'] }}</td>
                                            <td class="p-2">{{ $ins['descricao'] }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    </div>
                </li>
                @endforeach
            </ol>
        </div>
    </div>
</x-dynamic-component>
