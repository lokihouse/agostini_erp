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
                        <th scope="col" class="px-4 py-3 text-center border-s border-gray-200 dark:border-gray-600">
                            Comissão
                        </th>
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
                        <th scope="col" class="px-2 py-2 text-center text-xs border-s border-gray-200 dark:border-gray-600">Comissão</th>
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
                        {{-- 1) Linha principal: Nome + Vendido / Meta por mês + Totais Vendido/Meta --}}
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            {{-- Nome --}}
                            <td class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $salesperson['name'] }}
                            </td>

                            {{-- (Celula vazia no segundo slot porque a coluna de Comissão saiu para a linha abaixo) --}}
                            <td class="px-2 py-2"></td>

                            {{-- Meses: Vendido / Meta --}}
                            @foreach($monthHeaders as $monthDate)
                                @php
                                    $monthKey = $monthDate->format('Y-m');
                                    $monthData = $salesperson['months'][$monthKey] ?? ['sales' => 0, 'goal' => 0, 'performance' => null, 'difference' => 0, 'commission_amount' => 0, 'commission_type' => 'none'];
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

                            {{-- Totais: Vendido / Meta --}}
                            <td class="px-2 py-2 text-right border-s border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 font-semibold">
                                {{ number_format($salesperson['totals']['sales'], 2, ',', '.') }}
                            </td>
                            <td class="px-2 py-2 text-right bg-gray-50 dark:bg-gray-700 font-semibold">
                                {{ number_format($salesperson['totals']['goal'], 2, ',', '.') }}
                            </td>
                        </tr>

                        {{-- 2) Linha de Comissão: aparece abaixo do nome; mostra comissão por mês e total --}}
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-sm">
                            {{-- Primeiro td: label "Comissão" e total da comissão do período (embaixo do nome) --}}
                            <td class="px-4 py-1 text-gray-600 dark:text-gray-300 font-medium">
                                Comissão
                            </td>

                            {{-- Exibir total consolidado de comissão (logo ao lado do label) --}}
                            <td class="px-2 py-1 text-right font-semibold {{ $salesperson['totals']['commission'] > 0 ? 'text-green-600 dark:text-green-500' : 'text-gray-500 dark:text-gray-400' }}">
                                {{ number_format($salesperson['totals']['commission'], 2, ',', '.') }}
                            </td>

                            {{-- Comissão por mês (alinhada às colunas dos meses) --}}
                            @foreach($monthHeaders as $monthDate)
                                @php
                                    $monthKey = $monthDate->format('Y-m');
                                    $monthData = $salesperson['months'][$monthKey] ?? ['commission_amount' => 0, 'commission_type' => 'none', 'performance' => null, 'goal' => 0];
                                    // Visual para comissão por meta (textos e cores semelhantes ao que já tinha)
                                    $commissionClass = '';
                                    $commissionText = number_format($monthData['commission_amount'] ?? 0, 2, ',', '.');
                                    if (($monthData['commission_type'] ?? 'none') === 'goal') {
                                        if ($monthData['performance'] !== null && $monthData['performance'] < 100 && ($monthData['goal'] ?? 0) > 0) {
                                            $commissionClass = 'text-red-500 dark:text-red-400';
                                            $commissionText = 'Meta não alcançada';
                                        } elseif ($monthData['performance'] !== null && $monthData['performance'] >= 100) {
                                            $commissionClass = 'text-green-600 dark:text-green-500';
                                        } else {
                                            $commissionClass = 'text-gray-500 dark:text-gray-400';
                                            $commissionText = '-';
                                        }
                                    }
                                @endphp

                                <td class="px-2 py-1 text-right {{ $commissionClass }}">
                                    {{ $commissionText }}
                                </td>
                                {{-- coluna fantasma para alinhar com Meta (já que cada mês tem 2 colunas Vendido/Meta) --}}
                                <td class="px-2 py-1"></td>
                            @endforeach

                            {{-- Totais coluna da linha de comissão: deixamos vazia para alinhar com Totais acima (ou duplicar total se preferir) --}}
                            <td class="px-2 py-1 text-right bg-gray-50 dark:bg-gray-700 font-semibold"></td>
                            <td class="px-2 py-1 text-right bg-gray-50 dark:bg-gray-700 font-semibold"></td>
                        </tr>

                        {{-- 3) Linha Ating. / Dif. (mantida, abaixo da comissão) --}}
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-xs">
                            <td class="px-4 py-1 text-gray-500 dark:text-gray-400">↳ Ating. / Dif.</td>

                            {{-- célula vazia para alinhar com a coluna onde estava o total de comissão --}}
                            <td class="px-2 py-1 text-center"></td>

                            @foreach($monthHeaders as $monthDate)
                                @php
                                    $monthKey = $monthDate->format('Y-m');
                                    $monthData = $salesperson['months'][$monthKey] ?? ['sales' => 0, 'goal' => 0, 'performance' => null, 'difference' => 0];
                                    $performanceText = is_null($monthData['performance']) ? '-' : number_format($monthData['performance'], 1, ',', '.') . '%';
                                    $diffClass = $monthData['difference'] < 0 ? 'text-red-500 dark:text-red-400' : 'text-green-600 dark:text-green-500';
                                    $diffText = ($monthData['difference'] < 0 ? '(' : '') . number_format(abs($monthData['difference']), 2, ',', '.') . ($monthData['difference'] < 0 ? ')' : '');
                                @endphp

                                {{-- Performance --}}
                                <td class="px-2 py-1 text-right border-s border-gray-200 dark:border-gray-600 {{ $monthData['performance'] !== null && $monthData['performance'] < 100 && $monthData['goal'] > 0 ? 'text-red-500 dark:text-red-400' : ($monthData['performance'] !== null && $monthData['performance'] >= 100 ? 'text-green-600 dark:text-green-500' : '') }}">
                                    {{ $performanceText }}
                                </td>

                                {{-- Diferença --}}
                                <td class="px-2 py-1 text-right {{ $diffClass }}">
                                    {{ $diffText }}
                                </td>
                            @endforeach

                            {{-- Totais Performance --}}
                            <td class="px-2 py-1 text-right border-s border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 font-semibold {{ $salesperson['totals']['performance'] !== null && $salesperson['totals']['performance'] < 100 && $salesperson['totals']['goal'] > 0 ? 'text-red-500 dark:text-red-400' : ($salesperson['totals']['performance'] !== null && $salesperson['totals']['performance'] >= 100 ? 'text-green-600 dark:text-green-500' : '') }}">
                                {{ is_null($salesperson['totals']['performance']) ? '-' : number_format($salesperson['totals']['performance'], 1, ',', '.') . '%' }}
                            </td>

                            {{-- Totais Diferença --}}
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

