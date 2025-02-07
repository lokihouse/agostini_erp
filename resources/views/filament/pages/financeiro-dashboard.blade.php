<x-filament-panels::page>
    @if($relatorio->isEmpty())
        <div class="p-4 text-center text-gray-500">
            Nenhum dado encontrado.
        </div>
    @else
        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                    <tr>
                        <th colspan="4" scope="col" class="min-w-[200px] p-3">
                            Plano de contas
                        </th>
                        @for($data = $dataInicial->clone(); $data->lte($dataFinal); $data->endOfMonth()->addDay()->startOfDay())
                            <th scope="col" class="border-s text-center p-3 w-[100px]">
                                {{ $data->translatedFormat('M/y') }}
                            </th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach($relatorio as $item)
                        <tr class="bg-white border-b border-gray-200 font-bold text-gray-900">
                            <td colspan="4" class="px-2">
                                {{ $item->codigo }}. {{ $item->nome }}
                            </td>
                            @for($data = $dataInicial->clone(); $data->lte($dataFinal); $data->endOfMonth()->addDay()->startOfDay())
                                <td class="border-s text-right px-2">
                                    @php
                                        $valores = $item->getValores($data->clone()->startOfMonth(), $data->clone()->endOfMonth());
                                    @endphp
                                    @if($valores < 0)
                                        <span class="text-red-500">({{ number_format(abs($valores), 2, ',', '.') }})</span>
                                    @elseif($valores > 0)
                                        {{ number_format($valores, 2, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            @endfor
                        </tr>
                        @foreach($item->subContas as $firstLevelItem)
                            <tr class="bg-white border-b border-gray-200 font-medium text-gray-600">
                                <td class="w-4"></td>
                                <td colspan="3" class="px-2">
                                    {{ $firstLevelItem->codigo }}. {{ $firstLevelItem->nome }}
                                </td>
                                @for($data = $dataInicial->clone(); $data->lte($dataFinal); $data->endOfMonth()->addDay()->startOfDay())
                                    <td class="border-s text-right px-2">
                                        @php
                                            $valores = $firstLevelItem->getValores($data->clone()->startOfMonth(), $data->clone()->endOfMonth());
                                        @endphp
                                        @if($valores < 0)
                                            <span class="text-red-500">({{ number_format(abs($valores), 2, ',', '.') }})</span>
                                        @elseif($valores > 0)
                                            {{ number_format($valores, 2, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                            @foreach($firstLevelItem->subContas as $secondLevelItem)
                                <tr class="bg-white border-b font-thin text-gray-500">
                                    <td class="w-4"></td>
                                    <td class="w-4"></td>
                                    <td colspan="2" class="px-2">
                                        {{ $secondLevelItem->codigo }}. {{ $secondLevelItem->nome }}
                                    </td>
                                    @for($data = $dataInicial->clone(); $data->lte($dataFinal); $data->endOfMonth()->addDay()->startOfDay())
                                        <td class="border-s text-right px-2">
                                            @php
                                                $valores = $secondLevelItem->getValores($data->clone()->startOfMonth(), $data->clone()->endOfMonth());
                                            @endphp
                                            @if($valores < 0)
                                                <span class="text-red-500">({{ number_format(abs($valores), 2, ',', '.') }})</span>
                                            @elseif($valores > 0)
                                                {{ number_format($valores, 2, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    @endfor
                                </tr>
                                @foreach($secondLevelItem->subContas as $thirdLevelItem)
                                    <tr class="bg-white border-b font-thin text-gray-400">
                                        <td class="w-4"></td>
                                        <td class="w-4"></td>
                                        <td class="w-4"></td>
                                        <td class="px-2">
                                            {{ $thirdLevelItem->codigo }}. {{ $thirdLevelItem->nome }}
                                        </td>
                                        @for($data = $dataInicial->clone(); $data->lte($dataFinal); $data->endOfMonth()->addDay()->startOfDay())
                                            <td class="border-s text-right px-2">
                                                @php
                                                    $valores = $thirdLevelItem->getValores($data->clone()->startOfMonth(), $data->clone()->endOfMonth());
                                                @endphp
                                                @if($valores < 0)
                                                    <span class="text-red-500">({{ number_format(abs($valores), 2, ',', '.') }})</span>
                                                @elseif($valores > 0)
                                                    {{ number_format($valores, 2, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        @endfor
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-filament-panels::page>
