<x-filament-panels::page>
    <x-filament::section collapsible collapsed>
        <x-slot name="heading">
            Filtros
        </x-slot>
        <form wire:submit.prevent="gerarRelatorio">
            {{ $this->form }}

            <div class="mt-4">
                <x-filament::button type="submit">
                    Gerar Relatório
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    @if (!empty($dadosRelatorio))
        <div class="-mt-6 overflow-x-auto">
            <table class="fi-ta-table w-full text-xs table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                <thead>
                <tr class="bg-gray-50 dark:bg-white/5">
                    <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">OT</th>
                    <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">Status</th>
                    <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">Motorista</th>
                    <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">Veículo</th>
                    <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">Saída Prev.</th>
                    <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">Saída Efet.</th>
                    <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">Conclusão</th>
                    <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">Itens</th>
                    <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">% Aceitos</th>
                    <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">Justificativas Recusa</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                @forelse ($dadosRelatorio as $index => $linha)
                    <tr wire:key="report-row-{{ $index }}" class="{{ $loop->odd ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-white/5' }}">
                        <td class="fi-ta-cell p-0 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <div class="fi-ta-col-wrp px-3 py-4">
                                <a href="{{ \App\Filament\Resources\TransportOrderResource::getUrl('edit', ['record' => $linha['uuid']]) }}"
                                   target="_blank"
                                   class="text-primary-600 hover:text-primary-500 hover:underline">
                                    {{ $linha['numero_ot'] ?? 'N/A' }}
                                </a>
                            </div>
                        </td>
                        <td class="fi-ta-cell p-0 sm:first-of-type:ps-6 sm:last-of-type:pe-6"><div class="fi-ta-col-wrp px-3 py-4">{{ $linha['status'] ?? 'N/A' }}</div></td>
                        <td class="fi-ta-cell p-0 sm:first-of-type:ps-6 sm:last-of-type:pe-6"><div class="fi-ta-col-wrp px-3 py-4">{{ $linha['motorista'] ?? 'N/A' }}</div></td>
                        <td class="fi-ta-cell p-0 sm:first-of-type:ps-6 sm:last-of-type:pe-6"><div class="fi-ta-col-wrp px-3 py-4">{{ $linha['veiculo'] ?? 'N/A' }}</div></td>
                        <td class="fi-ta-cell p-0 sm:first-of-type:ps-6 sm:last-of-type:pe-6"><div class="fi-ta-col-wrp px-3 py-4">{{ $linha['data_saida_prevista'] ?? 'N/A' }}</div></td>
                        <td class="fi-ta-cell p-0 sm:first-of-type:ps-6 sm:last-of-type:pe-6"><div class="fi-ta-col-wrp px-3 py-4">{{ $linha['data_saida_efetiva'] ?? 'N/A' }}</div></td>
                        <td class="fi-ta-cell p-0 sm:first-of-type:ps-6 sm:last-of-type:pe-6"><div class="fi-ta-col-wrp px-3 py-4">{{ $linha['data_conclusao'] ?? 'N/A' }}</div></td>
                        <td class="fi-ta-cell p-0 sm:first-of-type:ps-6 sm:last-of-type:pe-6"><div class="fi-ta-col-wrp px-3 py-4 text-center">{{ $linha['total_itens'] ?? 'N/A' }}</div></td>
                        <td class="fi-ta-cell p-0 sm:first-of-type:ps-6 sm:last-of-type:pe-6"><div class="fi-ta-col-wrp px-3 py-4 text-center">{{ $linha['percentual_itens_aceitos'] ?? 'N/A' }}</div></td>
                        <td class="fi-ta-cell p-0 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <div class="fi-ta-col-wrp px-3 py-4 whitespace-normal max-w-xs truncate" title="{{ $linha['justificativas_recusados'] ?? '' }}">
                                {{ $linha['justificativas_recusados'] ?? 'N/A' }}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        {{-- Ajustar o colspan para o novo número de colunas --}}
                        <td colspan="11" class="fi-ta-empty-state-cell p-0 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                            <div class="fi-ta-empty-state px-6 py-12">
                                <div class="grid justify-center gap-y-4">
                                    <div class="fi-ta-empty-state-icon-ctn mx-auto rounded-full bg-gray-100 p-3 dark:bg-gray-500/20">
                                        <svg class="fi-ta-empty-state-icon h-6 w-6 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                        </svg>
                                    </div>
                                    <div class="fi-ta-empty-state-content text-center text-sm text-gray-500 dark:text-gray-400">
                                        Nenhuma linha encontrada.
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
            <div class="mt-4">
                {{-- Adicionar botão para exportar $dadosRelatorio para PDF/Excel --}}
            </div>
        </div>
    @else
        <p class="mt-6 text-center text-gray-500">Nenhum dado encontrado para os filtros selecionados.</p>
    @endif
</x-filament-panels::page>
