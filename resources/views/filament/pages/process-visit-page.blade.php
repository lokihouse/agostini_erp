<x-filament-panels::page>
    @if($record)
        <div class="space-y-2">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-2">
                    @if($client)
                        <x-filament::section collapsible>
                            <x-slot name="heading">
                                Informações do Cliente:
                            </x-slot>
                            <div class="grid grid-cols-1 gap-2 text-sm">
                                <p><strong>CNPJ:</strong> {{ $client->tax_number_formatted ?? $client->tax_number }}</p>
                                <p><strong>Nome Fantasia:</strong> {{ $client->name }}</p>
                                <p>
                                    <strong>Telefone:</strong> {{ $client->phone_number_formatted ?? $client->phone_number ?: 'N/A' }}
                                </p>
                                <p><strong>Email:</strong> {{ $client->email ?: 'N/A' }}</p>
                                <p><strong>Endereço:</strong> {{ $client->full_address ?: 'N/A' }}</p>
                            </div>
                        </x-filament::section>
                    @endif

                    <x-filament::section collapsible>
                        <x-slot name="heading">
                            Detalhes da Visita:
                        </x-slot>
                        <x-slot name="description">
                            Agendada para: {{ $record->scheduled_at->format('d/m/Y H:i') }}
                            @if($record->visit_start_time)
                                <br/>
                                <span class="mx-1">|</span> Iniciada
                                em: {{ $record->visit_start_time->format('d/m/Y H:i') }}
                            @endif
                        </x-slot>

                        <div class="space-y-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Status da Visita</div>
                                <div class="text-xs font-semibold text-gray-900 dark:text-white">
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                                        @switch($record->status)
                                            @case(\App\Models\SalesVisit::STATUS_SCHEDULED)
                                                bg-yellow-50 text-yellow-800 ring-yellow-600/20 dark:bg-yellow-400/10 dark:text-yellow-500 dark:ring-yellow-400/20
                                                @break
                                            @case(\App\Models\SalesVisit::STATUS_IN_PROGRESS)
                                                bg-blue-50 text-blue-800 ring-blue-600/20 dark:bg-blue-400/10 dark:text-blue-500 dark:ring-blue-400/20
                                                @break
                                            @case(\App\Models\SalesVisit::STATUS_COMPLETED)
                                                bg-green-50 text-green-800 ring-green-600/20 dark:bg-green-400/10 dark:text-green-500 dark:ring-green-400/20
                                                @break
                                            @case(\App\Models\SalesVisit::STATUS_CANCELLED)
                                                bg-red-50 text-red-800 ring-red-600/20 dark:bg-red-400/10 dark:text-red-500 dark:ring-red-400/20
                                                @break
                                            @default
                                                bg-gray-50 text-gray-800 ring-gray-600/20 dark:bg-gray-400/10 dark:text-gray-500 dark:ring-gray-400/20
                                        @endswitch
                                    ">
                                        {{ $record->getStatusOptions()[$record->status] ?? $record->status }}
                                    </span>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Vendedor</h3>
                                {{-- Garante que assignedTo está carregado --}}
                                <p class="text-xs text-gray-900 dark:text-white">{{ $record->assignedTo?->name ?? 'N/A' }}</p>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Pedido Associado</div>
                                <div>
                                    @if($record->salesOrder)
                                        <p class="text-xs text-gray-900 dark:text-white">
                                            <a href="{{ \App\Filament\Resources\SalesOrderResource::getUrl('edit', ['record' => $record->sales_order_id]) }}"
                                               class="text-primary-600 hover:underline" target="_blank">
                                                {{ $record->salesOrder->order_number }}
                                            </a>
                                        </p>
                                    @else
                                        <p class="text-xs text-gray-500 dark:text-gray-400 italic">Nenhum pedido
                                            gerado.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if($record->notes)
                            <div class="mt-4">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Observações da Visita
                                    (Agendamento)</h3>
                                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $record->notes }}</p>
                            </div>
                        @endif
                    </x-filament::section>
                </div>

                <div class="md:col-span-2" x-data="{ tab: 'tab1' }">
                    <x-filament::tabs label="Content tabs">
                        <x-filament::tabs.item @click="tab = 'tab1'" :alpine-active="'tab === \'tab1\''">
                            Processamento da Visita {{-- Nome da aba ajustado --}}
                        </x-filament::tabs.item>

                        <x-filament::tabs.item @click="tab = 'tab2'" :alpine-active="'tab === \'tab2\''">
                            Histórico de Pedidos {{-- Nome da aba ajustado --}}
                        </x-filament::tabs.item>
                    </x-filament::tabs>

                    <div class="mt-2">
                        <div x-show="tab === 'tab1'">
                            {{-- Relation Manager para Itens do Pedido --}}
                            @if ($record->salesOrder && $record->status === \App\Models\SalesVisit::STATUS_IN_PROGRESS)
                                <div class="mt-6">
                                    <x-filament::section :collapsible="true" :collapsed="false">
                                        <x-slot name="heading">
                                            Itens do Pedido: {{ $record->salesOrder->order_number }}
                                            (Status: {{ \App\Models\SalesOrder::getStatusOptions()[$record->salesOrder->status] ?? $record->salesOrder->status }})
                                        </x-slot>
                                        @livewire(\App\Filament\Resources\SalesOrderResource\RelationManagers\ItemsRelationManager::class, [
                                        'ownerRecord' => $record->salesOrder,
                                        'pageClass' => \App\Filament\Resources\SalesOrderResource\Pages\EditSalesOrder::class
                                        ])
                                    </x-filament::section>
                                </div>
                            @elseif ($record->status === \App\Models\SalesVisit::STATUS_IN_PROGRESS && !$record->salesOrder)
                                <div class="mt-6 p-4 text-center text-gray-500 dark:text-gray-400 border rounded-md bg-gray-50 dark:bg-gray-700/50">
                                    <p>Lance um novo pedido para adicionar itens.</p>
                                    <p class="text-xs mt-1">O botão "Lançar Novo Pedido" está disponível nas ações acima.</p>
                                </div>
                            @elseif ($record->status === \App\Models\SalesVisit::STATUS_SCHEDULED)
                                <div class="mt-6 p-4 text-center text-gray-500 dark:text-gray-400 border rounded-md bg-gray-50 dark:bg-gray-700/50">
                                    <p>Inicie a visita para processar o pedido e adicionar itens.</p>
                                    <p class="text-xs mt-1">O botão "Iniciar Visita" está disponível nas ações acima.</p>
                                </div>
                            @endif
                        </div>
                        <div x-show="tab === 'tab2'">
                            @if(!empty($pastOrders))
                                <x-filament::section>
                                    <x-slot name="heading">
                                        Últimos Pedidos do Cliente (Excluindo o da visita atual, se houver)
                                    </x-slot>
                                    <div class="flow-root -m-4">
                                        <ul role="list" class="">
                                            @foreach($pastOrders as $order)
                                                <li>
                                                    <div class="">
                                                        <div class="flex items-center justify-between w-full space-x-2 mb-4">
                                                            <div class="">
                                                                <span class="h-8 w-8 rounded-full flex items-center justify-center
                                                                    @switch($order['status'])
                                                                        @case(\App\Models\SalesOrder::STATUS_DELIVERED) bg-green-500 @break
                                                                        @case(\App\Models\SalesOrder::STATUS_CANCELLED) bg-red-500 @break
                                                                        @case(\App\Models\SalesOrder::STATUS_SHIPPED) bg-blue-500 @break
                                                                        @case(\App\Models\SalesOrder::STATUS_PROCESSING) bg-cyan-500 @break
                                                                        @case(\App\Models\SalesOrder::STATUS_APPROVED) bg-lime-500 @break
                                                                        @default bg-gray-400
                                                                    @endswitch
                                                                ">
                                                                    <x-heroicon-s-shopping-cart class="h-5 w-5 text-white"/>
                                                                </span>
                                                            </div>
                                                            <div class="min-w-0 flex-1">
                                                                <div class="flex">
                                                                    <div class="flex-1 text-sm text-gray-500 dark:text-gray-400">
                                                                        {{-- Link corrigido para a página de edição do pedido no Filament --}}
                                                                        <a href="{{ \App\Filament\Resources\SalesOrderResource::getUrl('edit', ['record' => $order['uuid']]) }}"
                                                                           class="font-medium text-gray-900 dark:text-white hover:underline" target="_blank">
                                                                            Pedido {{ $order['order_number'] }}
                                                                        </a>
                                                                    </div>
                                                                    <div
                                                                        class="whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                                                                        <time
                                                                            datetime="{{ $order['order_date'] }}">{{ \Carbon\Carbon::parse($order['order_date'])->format('d/m/Y') }}</time>
                                                                    </div>
                                                                </div>
                                                                <div class="border rounded-lg p-2">
                                                                    <div class="text-xs text-gray-500 dark:text-gray-400 grid grid-cols-1 sm:grid-cols-3 gap-2 text-center">
                                                                        <div class="sm:border-e">
                                                                            {{ \App\Models\SalesOrder::getStatusOptions()[$order['status']] ?? $order['status'] }}
                                                                        </div>
                                                                        <div class="sm:border-e">
                                                                            R$ {{ number_format($order['total_amount'], 2, ',', '.') }}
                                                                        </div>
                                                                        <div>
                                                                            @if($order['delivery_deadline'])
                                                                                <time datetime="{{ $order['delivery_deadline'] }}">{{ \Carbon\Carbon::parse($order['delivery_deadline'])->format('d/m/Y') }}</time>
                                                                            @else
                                                                                N/A
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </x-filament::section>
                            @else
                                <div class="mt-6 p-4 text-center text-gray-500 dark:text-gray-400 border rounded-md bg-gray-50 dark:bg-gray-700/50">
                                    <p>Nenhum pedido anterior encontrado para este cliente.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="flex flex-col items-center justify-center h-full p-8">
            <x-heroicon-o-exclamation-triangle class="w-16 h-16 text-danger-500"/>
            <p class="mt-4 text-xl font-semibold text-gray-700 dark:text-gray-200">Visita não encontrada ou
                inválida.</p>
            <p class="text-gray-500 dark:text-gray-400">Verifique o link ou contate o suporte.</p>
            <div class="mt-6">
                <x-filament::button tag="a" href="{{ \App\Filament\Resources\SalesVisitResource::getUrl('index') }}">
                    Voltar para Lista de Visitas
                </x-filament::button>
            </div>
        </div>
    @endif
</x-filament-panels::page>
