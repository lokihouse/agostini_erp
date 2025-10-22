<?php
    $currentYear = date('Y');
    $previousYear = date('Y') - 1;
?>

<x-filament-panels::page>
    </div>
     {{-- Barra de ações --}}
        <div class="flex items-center justify-end mb-6">
            <div class="flex space-x-2">
                <x-filament::button color="primary" icon="heroicon-o-printer" wire:click="downloadPreviousYearReport()">
                    Relatório <?= $previousYear?>
                </x-filament::button>

                <x-filament::button color="primary" icon="heroicon-o-printer" wire:click="downloadReport()">
                    Relatório <?= $currentYear ?>
                </x-filament::button>
            </div>
        </div>
        {{-- Card principal --}}
        <div class="bg-white shadow-sm rounded-xl p-4">
            {{-- Opção para preencher todos os meses --}}
            <div class="flex justify-end items-center mb-4">
                <label class="flex items-center space-x-2 text-sm text-gray-600">
                    <input type="checkbox" wire:model="replicateAllMonths" class="form-checkbox h-4 w-4 text-primary-600">
                    <span>Preencher todos os meses</span>
                </label>
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
