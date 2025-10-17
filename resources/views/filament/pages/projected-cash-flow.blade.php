<?php
    $currentYear = date('Y');
    $previousYear = date('Y') - 1;
?>

<x-filament-panels::page>
    <div class="flex items-center space-x-2 ml-auto">
        <label class="inline-flex items-center">
            <input type="checkbox" wire:model="replicateAllMonths" class="form-checkbox h-4 w-4 text-primary-600">
            <span class="ml-2 text-sm text-gray-700">Preencher todos os meses</span>
        </label>
        <button
            id="imprimir_relatorio"
            type="button"
            wire:click="downloadPreviousYearReport()"
            class="filament-button filament-button-size-md filament-button-color-primary inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 mr-1 -ml-1">
                <path fill-rule="evenodd" d="M5 2.75C5 1.784 5.784 1 6.75 1h6.5c.966 0 1.75.784 1.75 1.75v3.5A2.25 2.25 0 0 1 17.25 8.5H19a1 1 0 0 1 1 1v.75a.75.75 0 0 1-1.5 0V9.5h-.055a3.252 3.252 0 0 1-3.046-2.204L15.75 6.25v2.25A3.75 3.75 0 0 1 12 12.25H8A3.75 3.75 0 0 1 4.25 8.5v-2.5L3.105 7.296A3.25 3.25 0 0 1 .055 9.5H0V8.75a1 1 0 0 1 1-1h1.75A2.25 2.25 0 0 1 5 6.25v-3.5ZM6.5 2.5v3.75a.75.75 0 0 0 .75.75h5.5a.75.75 0 0 0 .75-.75V2.5h-7Z" clip-rule="evenodd" />
                <path d="M3.5 14A1.5 1.5 0 0 0 5 15.5h10A1.5 1.5 0 0 0 16.5 14v-1.5h-13V14Z" />
                <path d="M2 10.5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v1.879a.75.75 0 0 1-.36.644l-2.25 1.35A2.751 2.751 0 0 1 13.25 15H6.75a2.751 2.751 0 0 1-2.14-.627l-2.25-1.35A.75.75 0 0 1 2 12.379V10.5ZM3.5 12.621l2.25 1.35A1.252 1.252 0 0 0 6.75 14.5h6.5c.491 0 .942-.284 1.14-.729l2.25-1.35V12h-13v.621Z" />
            </svg>
            Relatório <?= $previousYear?>
        </button>
        <button
            id="imprimir_relatorio"
            type="button"
            wire:click="downloadReport"
            class="filament-button filament-button-size-md filament-button-color-primary inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 mr-1 -ml-1">
                <path fill-rule="evenodd" d="M5 2.75C5 1.784 5.784 1 6.75 1h6.5c.966 0 1.75.784 1.75 1.75v3.5A2.25 2.25 0 0 1 17.25 8.5H19a1 1 0 0 1 1 1v.75a.75.75 0 0 1-1.5 0V9.5h-.055a3.252 3.252 0 0 1-3.046-2.204L15.75 6.25v2.25A3.75 3.75 0 0 1 12 12.25H8A3.75 3.75 0 0 1 4.25 8.5v-2.5L3.105 7.296A3.25 3.25 0 0 1 .055 9.5H0V8.75a1 1 0 0 1 1-1h1.75A2.25 2.25 0 0 1 5 6.25v-3.5ZM6.5 2.5v3.75a.75.75 0 0 0 .75.75h5.5a.75.75 0 0 0 .75-.75V2.5h-7Z" clip-rule="evenodd" />
                <path d="M3.5 14A1.5 1.5 0 0 0 5 15.5h10A1.5 1.5 0 0 0 16.5 14v-1.5h-13V14Z" />
                <path d="M2 10.5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v1.879a.75.75 0 0 1-.36.644l-2.25 1.35A2.751 2.751 0 0 1 13.25 15H6.75a2.751 2.751 0 0 1-2.14-.627l-2.25-1.35A.75.75 0 0 1 2 12.379V10.5ZM3.5 12.621l2.25 1.35A1.252 1.252 0 0 0 6.75 14.5h6.5c.491 0 .942-.284 1.14-.729l2.25-1.35V12h-13v.621Z" />
            </svg>
            Relatório <?= $currentYear ?>
        </button>
    </div>
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th class="p-3 min-w-[300px]">Plano de Contas</th>
                    <th class="p-3 min-w-[120px] text-center">Meta</th> <!-- 🚀 nova coluna -->
                    @foreach($monthHeaders as $monthDate)
                        <th class="p-3 text-center">{{ \Carbon\Carbon::parse($monthDate)->translatedFormat('M/Y') }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                {{-- Linhas principais (recursivo) --}}
                @foreach($accounts as $account)
                    @include('filament.partials.cash-flow-row', [
                        'account' => $account,
                        'level' => 0,
                        'monthStrings' => $monthStrings
                    ])
                @endforeach
                
                {{-- Linha: Receita em Falta --}}
                @foreach($this->collectAllAccountsRecursively($accounts) as $account)
                    @php
                        $metaExiste = isset($cashFlowsMap[$account->uuid]['goal']) && $cashFlowsMap[$account->uuid]['goal'] > 0;
                    @endphp

                    @if($metaExiste)
                        <tr class="bg-gray-50">
                            <td colspan="2" class="font-bold text-gray-700">
                                Receita em Falta: {{ $account->code }}.{{ $account->name }}
                            </td>

                @foreach($monthStrings as $month)
                                @php
                                    $shortfall = $this->calculateShortfall($account->uuid, $month);

                                    // Definição da cor da célula:
                                    if (is_null($shortfall)) {
                                        $colorClass = 'text-gray-400 bg-gray-50';
                                    } elseif ($shortfall < 0) {
                                        // Ainda falta atingir a meta
                                        $colorClass = 'text-red-600 bg-red-100';
                                    } elseif ($shortfall == 0) {
                                        // Meta exatamente batida
                                        $colorClass = 'text-gray-700 bg-gray-200';
                                    } else {
                                        // Meta ultrapassada (receita > meta)
                                        $colorClass = 'text-green-700 bg-green-100';
                                    }

                                @endphp

                                <td class="text-right font-semibold {{ $colorClass }}">
                                    {{ number_format(abs($shortfall), 2, ',', '.') }}
                                </td>
                            @endforeach
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Notificações simples com BrowserEvent --}}
    <script>
        window.addEventListener('filament-notify', event => {
            const { type, message } = event.detail;
            if (typeof filament !== 'undefined' && filament.notify) {
                filament.notify({type, message});
            } else {
                alert(message);
            }
        });
    </script>
</x-filament-panels::page>
