<x-filament-panels::page>
{{--    <form class="absolute top-0 right-0 z-50 mb-6">--}}
{{--        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">--}}
{{--            {{ $this->form }}--}}

{{--            <div class="flex items-end">--}}
{{--                <x-filament::button type="submit">--}}
{{--                    Atualizar Relatório--}}
{{--                </x-filament::button>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </form>--}}

    @if($reportData->isEmpty())
        <x-filament::section>
            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                Nenhum plano de contas raiz encontrado para a sua empresa ou nenhum dado para o período selecionado.
            </div>
        </x-filament::section>
    @else
        <x-filament::section>
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th colspan="4" scope="col" class="min-w-[300px] p-3">
                            Plano de Contas
                        </th>
                        @foreach($monthHeaders as $monthDate)
                            <th scope="col" class="border-s border-gray-200 dark:border-gray-600 text-center p-3 w-[120px]">
                                {{ $monthDate->translatedFormat('M/y') }}
                            </th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($reportData as $item)
                        {{-- Root Level --}}
                        <tr class="text-[10px] bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 font-bold text-gray-900 dark:text-white">
                            <td colspan="4" class="px-3 py-2">
                                {{ $item->code }}. {{ $item->name }}
                            </td>
                            @foreach($monthHeaders as $monthDate)
                                <td class="border-s border-gray-200 dark:border-gray-600 text-right px-3 py-2">
                                    @php
                                        $value = $item->getValuesForPeriod($monthDate->copy()->startOfMonth(), $monthDate->copy()->endOfMonth());
                                    @endphp
                                    @if($value < 0)
                                        <span class="text-red-500 dark:text-red-400">({{ number_format(abs($value), 2, ',', '.') }})</span>
                                    @elseif($value > 0)
                                        {{ number_format($value, 2, ',', '.') }}
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        {{-- First Level Children --}}
                        @foreach($item->childAccounts as $firstChild)
                            <tr class="text-[10px] bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 font-medium text-gray-700 dark:text-gray-300">
                                <td class="w-1 text-transparent"></td>
                                <td colspan="3" class="px-3 py-2">
                                    {{ $firstChild->code }}. {{ $firstChild->name }}
                                </td>
                                @foreach($monthHeaders as $monthDate)
                                    <td class="border-s border-gray-200 dark:border-gray-600 text-right px-3 py-2">
                                        @php
                                            $value = $firstChild->getValuesForPeriod($monthDate->copy()->startOfMonth(), $monthDate->copy()->endOfMonth());
                                        @endphp
                                        @if($value < 0)
                                            <span class="text-red-500 dark:text-red-400">({{ number_format(abs($value), 2, ',', '.') }})</span>
                                        @elseif($value > 0)
                                            {{ number_format($value, 2, ',', '.') }}
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                            {{-- Second Level Children --}}
                            @foreach($firstChild->childAccounts as $secondChild)
                                <tr class="text-[10px] bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-400">
                                    <td class="w-1 text-transparent"></td>
                                    <td class="w-1 text-transparent"></td>
                                    <td colspan="2" class="px-3 py-2">
                                        {{ $secondChild->code }}. {{ $secondChild->name }}
                                    </td>
                                    @foreach($monthHeaders as $monthDate)
                                        <td class="border-s border-gray-200 dark:border-gray-600 text-right px-3 py-2">
                                            @php
                                                $value = $secondChild->getValuesForPeriod($monthDate->copy()->startOfMonth(), $monthDate->copy()->endOfMonth());
                                            @endphp
                                            @if($value < 0)
                                                <span class="text-red-500 dark:text-red-400">({{ number_format(abs($value), 2, ',', '.') }})</span>
                                            @elseif($value > 0)
                                                {{ number_format($value, 2, ',', '.') }}
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                {{-- Third Level Children --}}
                                @foreach($secondChild->childAccounts as $thirdChild)
                                    <tr class="text-[10px] bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 font-thin text-gray-500 dark:text-gray-400">
                                        <td class="w-1 text-transparent"></td>
                                        <td class="w-1 text-transparent"></td>
                                        <td class="w-1 text-transparent"></td>
                                        <td class="px-3 py-2">
                                            {{ $thirdChild->code }}. {{ $thirdChild->name }}
                                        </td>
                                        @foreach($monthHeaders as $monthDate)
                                            <td class="border-s border-gray-200 dark:border-gray-600 text-right px-3 py-2">
                                                @php
                                                    $value = $thirdChild->getValuesForPeriod($monthDate->copy()->startOfMonth(), $monthDate->copy()->endOfMonth());
                                                @endphp
                                                @if($value < 0)
                                                    <span class="text-red-500 dark:text-red-400">({{ number_format(abs($value), 2, ',', '.') }})</span>
                                                @elseif($value > 0)
                                                    {{ number_format($value, 2, ',', '.') }}
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                    {{-- You can continue nesting if needed, or consider a recursive Blade component for deeper hierarchies --}}
                                @endforeach
                            @endforeach
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
