<x-filament-panels::page>
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

    @if($reportData->isNotEmpty())
        <x-filament::section>
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-4 py-3">Cliente</th>
                        <th scope="col" class="px-4 py-3">Vendedor</th>
                        <th scope="col" class="px-4 py-3">Data Finalização</th>
                        <th scope="col" class="px-4 py-3">Motivo (Sem Pedido)</th>
                        <th scope="col" class="px-4 py-3">Ações Corretivas</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($reportData as $visit)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-4 py-2 font-medium text-gray-900 dark:text-white">
                                {{ $visit->client?->name ?? 'N/A' }}
                                @if($visit->client?->tax_number)
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $visit->client->tax_number_formatted }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                                {{ $visit->assignedTo?->name ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                                {{ $visit->visit_end_time ? $visit->visit_end_time->format('d/m/Y H:i') : ($visit->visited_at ? $visit->visited_at->format('d/m/Y H:i') : 'N/A') }}
                            </td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300 text-xs">
                                {{ $visit->report_reason_no_order ?: '-' }}
                            </td>
                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300 text-xs">
                                {{ $visit->report_corrective_actions ?: '-' }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @if ($reportData instanceof \Illuminate\Contracts\Pagination\Paginator && $reportData->hasPages())
                <div class="p-2">
                    {{ $reportData->links() }}
                </div>
            @endif

        </x-filament::section>
    @else
        <x-filament::section>
            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                Nenhuma visita finalizada sem pedido encontrada para os filtros selecionados.
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
