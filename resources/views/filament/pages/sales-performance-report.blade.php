<x-filament-panels::page>
    <x-filament::section collapsible collapsed>
        <x-slot name="heading">
            Filtros
        </x-slot>
        <form wire:submit.prevent="submitFilters" class="mb-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                {{ $this->form }}

                <div class="flex items-end">
                    <x-filament::button type="submit">
                        Aplicar Filtros
                    </x-filament::button>
                </div>
            </div>
        </form>
    </x-filament::section>

    @if(!empty($reportData))
        <x-filament::section>
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-4 py-3 min-w-[200px]">Vendedor</th>
                        @foreach($monthHeaders as $monthDate)
                            <th scope="col" colspan="2" class="px-4 py-3 text-center border-s border-gray-200 dark:border-gray-600">
                                {{ $monthDate->translatedFormat('M/y') }}
                            </th>
                        @endforeach
                        <th scope="col" colspan="2" class="px-4 py-3 text-center border-s border-gray-200 dark:border-gray-600 bg-gray-200 dark:bg-gray-600">
                            TOTAL PERÍODO
                        </th>
                    </tr>
                    <tr>
                        <th scope="col" class="px-4 py-3"></th>
                        @foreach($monthHeaders as $monthDate)
                            <th scope="col" class="px-2 py-2 text-center text-xs border-s border-gray-200 dark:border-gray-600">Vendido</th>
                            <th scope="col" class="px-2 py-2 text-center text-xs">Meta</th>
                        @endforeach
                        <th scope="col" class="px-2 py-2 text-center text-xs border-s border-gray-200 dark:border-gray-600 bg-gray-200 dark:bg-gray-600">Vendido</th>
                        <th scope="col" class="px-2 py-2 text-center text-xs bg-gray-200 dark:bg-gray-600">Meta</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($reportData as $salesperson)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $salesperson['name'] }}
                            </td>
                            @foreach($monthHeaders as $monthDate)
                                @php
                                    $monthKey = $monthDate->format('Y-m');
                                    $monthData = $salesperson['months'][$monthKey] ?? ['sales' => 0, 'goal' => 0, 'performance' => null, 'difference' => 0];
                                @endphp
                                {{-- Vendido --}}
                                <td class="px-2 py-2 text-right border-s border-gray-200 dark:border-gray-600">
                                    {{ number_format($monthData['sales'], 2, ',', '.') }}
                                </td>
                                {{-- Meta --}}
                                <td class="px-2 py-2 text-right">
                                    {{ number_format($monthData['goal'], 2, ',', '.') }}
                                </td>
                            @endforeach
                            {{-- TOTAL Vendido --}}
                            <td class="px-2 py-2 text-right border-s border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 font-semibold">
                                {{ number_format($salesperson['totals']['sales'], 2, ',', '.') }}
                            </td>
                            {{-- TOTAL Meta --}}
                            <td class="px-2 py-2 text-right bg-gray-50 dark:bg-gray-700 font-semibold">
                                {{ number_format($salesperson['totals']['goal'], 2, ',', '.') }}
                            </td>
                        </tr>
                        {{-- Linha de Performance e Diferença --}}
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-xs">
                            <td class="px-4 py-1 text-gray-500 dark:text-gray-400">↳ Ating. / Dif.</td>
                            @foreach($monthHeaders as $monthDate)
                                @php
                                    $monthKey = $monthDate->format('Y-m');
                                    $monthData = $salesperson['months'][$monthKey] ?? ['sales' => 0, 'goal' => 0, 'performance' => null, 'difference' => 0];
                                    $performanceText = is_null($monthData['performance']) ? '-' : number_format($monthData['performance'], 1, ',', '.') . '%';
                                    $diffClass = $monthData['difference'] < 0 ? 'text-red-500 dark:text-red-400' : 'text-green-600 dark:text-green-500';
                                    $diffText = ($monthData['difference'] < 0 ? '(' : '') . number_format(abs($monthData['difference']), 2, ',', '.') . ($monthData['difference'] < 0 ? ')' : '');
                                @endphp
                                <td class="px-2 py-1 text-right border-s border-gray-200 dark:border-gray-600 {{ $monthData['performance'] !== null && $monthData['performance'] < 100 && $monthData['goal'] > 0 ? 'text-red-500 dark:text-red-400' : ($monthData['performance'] !== null && $monthData['performance'] >= 100 ? 'text-green-600 dark:text-green-500' : '') }}">
                                    {{ $performanceText }}
                                </td>
                                <td class="px-2 py-1 text-right {{ $diffClass }}">
                                    {{ $diffText }}
                                </td>
                            @endforeach
                            {{-- TOTAL Performance --}}
                            <td class="px-2 py-1 text-right border-s border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 font-semibold {{ $salesperson['totals']['performance'] !== null && $salesperson['totals']['performance'] < 100 && $salesperson['totals']['goal'] > 0 ? 'text-red-500 dark:text-red-400' : ($salesperson['totals']['performance'] !== null && $salesperson['totals']['performance'] >= 100 ? 'text-green-600 dark:text-green-500' : '') }}">
                                {{ is_null($salesperson['totals']['performance']) ? '-' : number_format($salesperson['totals']['performance'], 1, ',', '.') . '%' }}
                            </td>
                            {{-- TOTAL Diferença --}}
                            <td class="px-2 py-1 text-right bg-gray-50 dark:bg-gray-700 font-semibold {{ $salesperson['totals']['difference'] < 0 ? 'text-red-500 dark:text-red-400' : 'text-green-600 dark:text-green-500' }}">
                                {{ ($salesperson['totals']['difference'] < 0 ? '(' : '') . number_format(abs($salesperson['totals']['difference']), 2, ',', '.') . ($salesperson['totals']['difference'] < 0 ? ')' : '') }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @else
        <x-filament::section>
            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                Nenhum dado encontrado para os filtros selecionados ou nenhum vendedor cadastrado.
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>

