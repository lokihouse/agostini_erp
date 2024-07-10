@php
use Carbon\CarbonInterval;
@endphp
<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{ state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }} }">
        <div
            class="fi-ta-ctn divide-y divide-gray-200 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">
            <div
                class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10">
                <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                    <thead class="divide-y divide-gray-200 dark:divide-white/5">
                    <tr class="bg-gray-50 dark:bg-white/5">

                        <th class="fi-ta-header-cell p-2 ps-6 w-1">
                            &nbsp;
                        </th>

                        <th class="fi-ta-header-cell p-2">
                            <span class="group flex w-full items-center whitespace-nowrap justify-start">
                                <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                    Depto. Origem
                                </span>
                            </span>
                        </th>

                        <th class="fi-ta-header-cell p-2">
                            <span class="group flex w-full items-center whitespace-nowrap justify-start">
                                <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                    Insumos
                                </span>
                            </span>
                        </th>

                        <th class="fi-ta-header-cell p-2">
                            <span class="group flex w-full items-center whitespace-nowrap justify-start">
                                <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                    Depto. Destino
                                </span>
                            </span>
                        </th>

                        <th class="fi-ta-header-cell p-2">
                            <span class="group flex w-full items-center whitespace-nowrap justify-start">
                                <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                    Produtos
                                </span>
                            </span>
                        </th>

                        <th class="fi-ta-header-cell p-2 pe-6">
                            <span class="group flex w-full items-center whitespace-nowrap justify-start">
                                <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                    Tempo
                                </span>
                            </span>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                    @if(count($this->getRecord()->etapas) === 0)
                        <tr>
                            <td colspan="6" class="text-center p-2">Nenhuma etapa atribuída ainda.</td>
                        </tr>
                    @endif
                    @foreach($this->getRecord()->etapas as $etapa)
                        <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="fi-ta-cell" style="border-right: 1px solid rgb(229, 231, 235)">
                                <div class="fi-ta-col-wrp">
                                    <div class="fi-ta-text grid w-full px-6">
                                        <x-filament::icon-button
                                            wire:click="deleteEtapa({{ $etapa->id }})"
                                            icon="heroicon-m-trash"
                                            size="xs"
                                            color="danger"
                                            label="Apagar"
                                        />
                                    </div>
                                </div>
                            </td>

                            <td class="fi-ta-cell p-0" style="border-right: 1px solid rgb(229, 231, 235)">
                                <div class="fi-ta-col-wrp">
                                    <div class="fi-ta-text grid w-full p-2">
                                        <div class="flex ">
                                            <div class="flex max-w-max" style="">
                                                <div class="fi-ta-text-item inline-flex items-center gap-1.5  ">
                                                    <span
                                                        class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  "
                                                        style="">
                                                        {{ $etapa->departamento_id_origem_nome }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="fi-ta-cell p-0" style="border-right: 1px solid rgb(229, 231, 235)">
                                <div class="fi-ta-col-wrp">
                                    <div class="fi-ta-text grid w-full p-2">
                                        <div class="flex ">
                                            <div class="flex max-w-max" style="">
                                                <div class="fi-ta-text-item inline-flex items-center gap-1.5  ">
                                                    <span
                                                        class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  "
                                                        style="">
                                                        <ul>
                                                        @foreach(json_decode($etapa->insumos) as $insumo)
                                                                <li>{{$insumo}}</li>
                                                            @endforeach
                                                        </ul>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="fi-ta-cell p-0" style="border-right: 1px solid rgb(229, 231, 235)">
                                <div class="fi-ta-col-wrp">
                                    <div class="fi-ta-text grid w-full p-2">
                                        <div class="flex ">
                                            <div class="flex max-w-max" style="">
                                                <div class="fi-ta-text-item inline-flex items-center gap-1.5  ">
                                                    <span
                                                        class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  "
                                                        style="">
                                                        {{ $etapa->departamento_id_destino_nome }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="fi-ta-cell p-0" style="border-right: 1px solid rgb(229, 231, 235)">
                                <div class="fi-ta-col-wrp">
                                    <div class="fi-ta-text grid w-full p-2">
                                        <div class="flex ">
                                            <div class="flex max-w-max" style="">
                                                <div class="fi-ta-text-item inline-flex items-center gap-1.5  ">
                                                    <span
                                                        class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  "
                                                        style="">
                                                        <ul>
                                                        @foreach(json_decode($etapa->producao) as $prod)
                                                            <li>{{$prod}}</li>
                                                        @endforeach
                                                        </ul>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="fi-ta-cell p-0">
                                <div class="fi-ta-col-wrp">
                                    <div class="fi-ta-text grid w-full p-2 pe-6">
                                        <div class="flex ">
                                            <div class="flex max-w-max" style="">
                                                <div class="fi-ta-text-item inline-flex items-center gap-1.5  ">
                                                    <span
                                                        class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  "
                                                        style="">
                                                        {{ $etapa->tempo_producao > 0 ? CarbonInterval::seconds($etapa->tempo_producao)->cascade()->forHumans(['short' => true]) : '-' }}
                                                    </span>
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
        </div>
    </div>
</x-dynamic-component>
