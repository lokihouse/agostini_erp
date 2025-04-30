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

        foreach ($st['origens'] as $origem) {
            $edges[] = [
                'from' => $origem['produto_etapa_id_origem'] === null ? 0 : $origem['produto_etapa_id_origem'],
                'to' => $origem['produto_etapa_id'],
                'color' => 'black',
                'arrows' => [
                    'to' => [
                        'enabled' => true
                    ]
                ]
            ];
        }

        foreach ($st['destinos'] as $destinos) {
            $edges[] = [
                'from' => $destinos['produto_etapa_id'],
                'to' => $destinos['produto_etapa_id_destino'] === null ? -1 : $destinos['produto_etapa_id_destino'],
                'color' => 'black',
                'arrows' => [
                    'to' => [
                        'enabled' => true
                    ]
                ]
            ];
        }
    }
}else {
    $state = [];
}
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div class="rounded-xl w-full p-2 bg-gray-100 flex justify-end gap-x-2">
        {{ $field->getAction('addStep') }}
    </div>
    <div class="grid grid-cols-1 gap-2">
        <div class="rounded-xl">
            <div class="bg-gray-100 dark:border-gray-700 border-gray-200 border-2 rounded-xl">
                @livewire('vis-js-network', ['nodes' => $nodes, 'edges' => $edges])
            </div>
        </div>



        <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow-sm sm:p-8 dark:bg-gray-800 dark:border-gray-700">
            <div class="flow-root">
                <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($state as $st)
                    <li class="py-3 sm:py-4">
                        <div class="flex items-start">
                            <div class="shrink-0">
                                {{ $field->getAction('excluirStep')(['produto_etapa_id' => $st['id']]) }}
                            </div>
                            <div class="flex-1 ms-4">
                                <div class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                    {{ $st['nome'] }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $st['descricao'] ?? '-' }}
                                </div>
                                <div class="grid grid-cols-2 gap-4 text-xs">
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
                                                @foreach(json_decode($st['insumos'] ?? '[]', true) as $ins)
                                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                                                        <td class="p-2 border-e">{{ $ins['quantidade'] }}</td>
                                                        <td class="p-2">{{ $ins['descricao'] }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
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
                                                @foreach(json_decode($st['producao'] ?? '[]', true) as $ins)
                                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                                                        <td class="p-2 border-e">{{ $ins['quantidade'] }}</td>
                                                        <td class="p-2">{{ $ins['descricao'] }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="min-w-[70px] inline-flex justify-end items-center text-base font-semibold text-gray-900 dark:text-white">
                                {{ $st['tempo_de_producao'] }}
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-dynamic-component>


