<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }">
        <div class="grid auto-cols-fr gap-y-2">
            <div class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 fi-disabled bg-gray-50 dark:bg-transparent ring-gray-950/10 dark:ring-white/10 fi-fo-key-value">
                <div class="min-w-0 flex-1">
                    <div class="divide-y divide-gray-200 dark:divide-white/10">
                        <table class="w-full table-auto divide-y divide-gray-200 dark:divide-white/5">
                            @php
                            $finalizados = 0;
                            $totais = 0;
                            @endphp
                            <thead>
                                <tr>
                                    <th scope="col" class="px-3 py-2 text-start text-sm font-medium text-gray-700 dark:text-gray-200">
                                        Quantidade
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-start text-sm font-medium text-gray-700 dark:text-gray-200">
                                        Item
                                    </th>
                                    <th scope="col" class="px-3 py-2 text-start text-sm font-medium text-gray-700 dark:text-gray-200">
                                        Andamento
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                            @foreach(json_decode($getRecord()->produtos, true) as $produto)
                                @php
                                    $andamento_finalizados = array_reduce($produto['etapas'], function ($carry, $newer){ return $newer["finalizada"] ? ++$carry : $carry; }, 0);
                                    $andamento_totais = count($produto['etapas']) ?? 0;
                                    $finalizados += $andamento_finalizados;
                                    $totais += $andamento_totais;
                                @endphp
                                <tr class="divide-x divide-gray-200 dark:divide-white/5 rtl:divide-x-reverse">
                                <td class="w-1 p-0">
                                    <div class="block w-full text-center border-none py-1.5 text-sm transition duration-75 placeholder:text-gray-400 ps-3 pe-3 font-mono">
                                        {{ $produto['quantidade'] }}
                                    </div>
                                </td>

                                <td class="p-0">
                                    <div class="block w-full border-none py-1.5 text-sm transition duration-75 placeholder:text-gray-400 ps-3 pe-3 font-mono">
                                        {{ $produto['nome'] }}
                                    </div>
                                </td>

                                <td class="w-1 p-0">
                                    <div class="text-center text-mono text-xs"> {{ $andamento_finalizados }} / {{ $andamento_totais }} </div>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>

                            <tfoot>
                            @php
                            $totalizado = number_format( (($finalizados * 100) / ($totais)), 2 );
                            @endphp
                            <tr>
                                <th colspan="2" scope="col" class="px-3 py-2 text-start text-sm font-medium text-gray-700 dark:text-gray-200">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                        <div class="bg-primary-600 h-2.5 rounded-full" style="width: {{$totalizado}}%"></div>
                                    </div>
                                </th>
                                <th scope="col" class="px-3 py-2 text-center text-xs font-bold text-gray-700 dark:text-gray-200">
                                    {{ $totalizado }}%
                                </th>
                            </tr>
                            </tfoot>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>
