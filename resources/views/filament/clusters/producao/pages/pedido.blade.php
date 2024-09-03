@php
    use App\Http\Controllers\ProdutoController;
    use App\Models\OrdemDeProducao;
    use App\Models\Produto;

    $ordem = $ordem ?? $this->getRecord();

    $ordemDeProducao = OrdemDeProducao::query()->find($ordem['id']);
    $etapas = [];
    foreach ($ordemDeProducao->produtos_na_ordem as $s){
        if(empty($s['produto_id'])) continue;
        $etapas = array_merge($etapas, ProdutoController::getEtapasMapeadas(Produto::query()->find($s['produto_id']))->toArray());
    }
    $etapas = array_unique($etapas, SORT_REGULAR);
    $diagraph = ProdutoController::getDiagraph($etapas);
    $imagem = ProdutoController::runDotCommand($diagraph);

    $ordem['mapa_producao'] = $imagem;

    function formatStatus($status) {
        return match($status) {
            'agendada' => 'Agendada',
            'em_producao' => 'Em Produção',
            default => $status,
        };
    }
@endphp
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<script src="https://cdn.tailwindcss.com"></script>
<style>
    * {
        padding: 0;
        margin: 0;
    }
</style>
<div class="w-full flex-1 flex-col flex">
    <div class="space-y-4">
        <nav class="flex h-16 items-center gap-x-4 px-4">
            <div class="me-6">
                <div class="flex text-xl font-bold leading-5 tracking-tight text-gray-950">
                    AGOSTINI
                </div>
                <div class="text-left">
                    <div class="text-base font-bold leading-5 tracking-tight text-gray-950">ORDEM DE PRODUÇÃO</div>
                    <div class="text-xs font-light text-gray-500">#{{$ordem['id']}}
                        - {{ formatStatus($ordem['status']) }}</div>
                </div>
            </div>

            <div class="ms-auto flex items-center gap-x-4">
                <div>
                    <img
                        src="data:image/png;base64,{{ Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($ordem['status'] . '|' . $ordem['id'], 'C128') }}"
                        alt="barcode"/>
                </div>
            </div>
        </nav>

        <main class="px-4 space-y-4">
            <x-filament::section icon="fas-calendar-days" compact>
                <x-slot name="heading">
                    Cronograma
                </x-slot>

                <div class="grid grid-cols-2 gap-x-2">
                    <div>
                        <x-filament::fieldset>
                            <x-slot name="label">
                                Agendamento
                            </x-slot>
                            {{ \Carbon\Carbon::parse($ordem['data_inicio_agendamento'])->format('d/m/Y') }}
                            à {{ \Carbon\Carbon::parse($ordem['data_final_agendamento'])->format('d/m/Y') }}
                        </x-filament::fieldset>
                    </div>
                    <div>
                        <x-filament::fieldset>
                            <x-slot name="label">
                                Produção
                            </x-slot>
                            @if($ordem['status'] === 'agendada')
                                <span class="">Não iniciada</span>
                            @else
                                {{ \Carbon\Carbon::parse($ordem['data_inicio_producao'])->format('d/m/Y') }}
                                à {{ \Carbon\Carbon::parse($ordem['data_final_producao'])->format('d/m/Y') }}
                            @endif
                        </x-filament::fieldset>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section icon="fas-diagram-project" compact>
                <x-slot name="heading">
                    Produção
                </x-slot>

                <div class="grid grid-cols-2 gap-x-2">
                    <div>
                        <x-filament::fieldset>
                            <x-slot name="label">
                                Produtos
                            </x-slot>
                            <div
                                class="fi-ta-content border-2 border-gray-100 rounded-xl relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10 !border-t-0">
                                <table
                                    class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                                    <thead class="divide-y divide-gray-200 dark:divide-white/5">
                                    <tr class="bg-gray-50 dark:bg-white/5">
                                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 fi-table-header-cell-quantidade w-1">
                                        <span
                                            class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                            <span
                                                class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                                Quant.
                                            </span>
                                        </span>
                                        </th>

                                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 fi-table-header-cell-produto.nome">
                                        <span
                                            class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                            <span
                                                class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                                Produto
                                            </span>
                                        </span>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                                    @foreach($ordem['produtos_na_ordem'] as $produto_na_ordem)
                                        <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75">
                                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-table-cell-quantidade">
                                                <div class="fi-ta-col-wrp">
                                                    <div
                                                        class="flex w-full disabled:pointer-events-none justify-start text-start">
                                                        <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                                                            <div class="flex ">
                                                                <div class="flex max-w-max">
                                                                    <div
                                                                        class="fi-ta-text-item inline-flex items-center gap-1.5  ">
                                                                <span
                                                                    class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  ">
                                                                    {{ $produto_na_ordem['quantidade']  }}
                                                                </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-table-cell-produto.nome">
                                                <div class="fi-ta-col-wrp">
                                                    <div
                                                        class="flex w-full disabled:pointer-events-none justify-start text-start">
                                                        <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                                                            <div class="flex ">
                                                                <div class="flex max-w-max" style="">
                                                                    <div
                                                                        class="fi-ta-text-item inline-flex items-center gap-1.5  ">
                                                                <span
                                                                    class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white">
                                                                    {{ $produto_na_ordem['produto']['nome']  }}
                                                                </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </x-filament::fieldset>
                    </div>
                    <div>
                        <x-filament::fieldset>
                            <x-slot name="label">
                                Mapa de Etapas
                            </x-slot>
                            <div class="flex justify-center">
                                <img class="h-[250px]" src='{{ $ordem['mapa_producao']  }}'/>
                            </div>
                        </x-filament::fieldset>
                    </div>
                </div>
            </x-filament::section>

            @php

            $etapas = [];

            foreach ($ordem['produtos_na_ordem'] as $produto_na_ordem){
                foreach ($produto_na_ordem->produto->produto_etapas as $produto_etapa){
                    $out = [
                        "etapa_id" => $produto_etapa->id,
                        "departamento_origem_nome" => $produto_etapa->departamento_origem['nome'],
                        "equipamento_origem_nome" => $produto_etapa->equipamento_origem['nome'] ?? null,
                        "departamento_destino_nome" => $produto_etapa->departamento_destino['nome'],
                        "equipamento_destino_nome" => $produto_etapa->equipamento_destino['nome'] ?? null,
                        "producao" => json_decode($produto_etapa->producao),
                        // ...$produto_etapa->toArray()
                    ];
                    $etapas[] = $out;
                }
                // $etapas = array_merge($etapas, $produto_etapa->toArray());
            }

            @endphp

            @foreach($etapas as $key => $etapa)
            <x-filament::section icon="fas-forward-step" compact>
                <x-slot name="heading">
                    Etapa {{ $key + 1 }} - {{ $etapa["departamento_origem_nome"] }} {{ $etapa["equipamento_origem_nome"] ?: '' }} para {{ $etapa["departamento_destino_nome"] }} {{ $etapa["equipamento_destino_nome"] ?: '' }}
                </x-slot>

                <x-slot name="headerEnd">
                    <img
                        src="data:image/png;base64,{{ Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($ordem['status'] . '|' . $ordem['id']. '|' . $etapa['etapa_id'], 'C128') }}"
                        alt="barcode"/>
                </x-slot>

                {{ json_encode($etapa) }}
            </x-filament::section>
            @endforeach
        </main>
        @pageBreak

        <div class="text-xs font-light text-gray-400">
            <pre>{{ json_encode($ordem, JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
</div>
