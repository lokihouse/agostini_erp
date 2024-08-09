@php
    $tabs = \App\Models\PlanoDeConta::query()->where('plano_de_conta_id', $this->data['id'])->get()->toArray();
@endphp
<x-filament-panels::page>
    @if(count($tabs))
        <section class="">
            <table class="w-full rounded-xl bg-gray-200 shadow-sm dark:bg-gray-900 dark:ring-white/10">
                <thead>
                <tr class="">
                    <th class="w-[75px] py-2 px-4">Cód</th>
                    <th class="w-[75px] py-2 px-4"></th>
                    <th class="w-[75px] py-2 px-4"></th>
                    <th class="w-[75px] py-2 px-4"></th>
                    <th class="w-[75px] py-2 px-4"></th>
                    <th class="max-w-fit py-2 px-4"></th>
                    <th class="w-[200px] py-2 px-4 text-right">Realizado</th>
                    <th class="w-[200px] py-2 px-4 text-right">Projetado</th>
                </tr>
                </thead>
                <tbody class="rounded-b-xl">
                @foreach($tabs as $tab)
                    <tr class="font-extrabold bg-gray-300 cursor-pointer"
                        x-on:click="window.location.href = '{{ route('filament.app.financeiro.resources.plano-de-contas.edit', [$tab['id']]) }}'">
                        <td class="pl-4 text-right">
                            <a href="{{ route('filament.app.financeiro.resources.plano-de-contas.edit', [$tab['id']]) }}">
                                {{ $tab['codigo'] }}
                            </a>
                        </td>
                        <td colspan="5" class="px-4">
                            {{ $tab['descricao'] }}
                        </td>
                        <td class="text-right pr-4 border-right border-gray-200">
                            @if($tab['valor_realizado'] >= 0)
                                {{ \App\Utils\MyTextFormater::toMoney($tab['valor_realizado'] ?? 0) }}
                            @else
                                @php
                                    $val = floatval($tab['valor_realizado'] ?? 0) * -1;
                                @endphp
                                <span class="text-red-500">({{ \App\Utils\MyTextFormater::toMoney($val) }})</span>
                            @endif
                        </td>
                        <td class="pr-4 text-right">
                            @if($tab['valor_projetado'] >= 0)
                                {{ \App\Utils\MyTextFormater::toMoney($tab['valor_projetado'] ?? 0) }}
                            @else
                                @php
                                    $val = floatval($tab['valor_projetado'] ?? 0) * -1;
                                @endphp
                                <span class="text-red-500">({{ \App\Utils\MyTextFormater::toMoney($val) }})</span>
                            @endif
                        </td>
                    </tr>
                    @php
                        $contas_1 = \App\Models\PlanoDeConta::query()->where('plano_de_conta_id', $tab['id'])->get()->toArray();
                    @endphp
                    @foreach($contas_1 as $tab_1)
                        <tr class="bg-gray-200 font-medium  cursor-pointer"
                            x-on:click="window.location.href = '{{ route('filament.app.financeiro.resources.plano-de-contas.edit', [$tab_1['id']]) }}'">
                            <td colspan="2" class="pl-4 bg-gray-200 text-right">
                                {{ $tab_1['codigo'] }}
                            </td>
                            <td colspan="4" class="px-4">
                                {{ $tab_1['descricao'] }}
                            </td>
                            <td class="text-right pr-4 border-right border-gray-200">
                                @if($tab_1['valor_realizado'] >= 0)
                                    {{ \App\Utils\MyTextFormater::toMoney($tab_1['valor_realizado'] ?? 0) }}
                                @else
                                    @php
                                        $val = floatval($tab_1['valor_realizado'] ?? 0) * -1;
                                    @endphp
                                    <span class="text-red-500">({{ \App\Utils\MyTextFormater::toMoney($val) }})</span>
                                @endif
                            </td>
                            <td class="pr-4 text-right">
                                @if($tab_1['valor_projetado'] >= 0)
                                    {{ \App\Utils\MyTextFormater::toMoney($tab_1['valor_projetado'] ?? 0) }}
                                @else
                                    @php
                                        $val = floatval($tab_1['valor_projetado'] ?? 0) * -1;
                                    @endphp
                                    <span class="text-red-500">({{ \App\Utils\MyTextFormater::toMoney($val) }})</span>
                                @endif
                            </td>
                        </tr>
                        @php
                            $contas_2 = \App\Models\PlanoDeConta::query()->where('plano_de_conta_id', $tab_1['id'])->get()->toArray();
                        @endphp
                        @foreach($contas_2 as $tab_2)
                            <tr class="bg-gray-200 font-light  cursor-pointer"
                                x-on:click="window.location.href = '{{ route('filament.app.financeiro.resources.plano-de-contas.edit', [$tab_2['id']]) }}'">
                                <td colspan="3" class="pl-4 text-right">
                                    {{ $tab_2['codigo'] }}
                                </td>
                                <td colspan="3">
                                    {{ $tab_2['descricao'] }}
                                </td>
                                <td class="text-right pr-4 border-right border-gray-200">
                                    @if($tab_2['valor_realizado'] >= 0)
                                        {{ \App\Utils\MyTextFormater::toMoney($tab_2['valor_realizado'] ?? 0) }}
                                    @else
                                        @php
                                            $val = floatval($tab_2['valor_realizado'] ?? 0) * -1;
                                        @endphp
                                        <span class="text-red-500">({{ \App\Utils\MyTextFormater::toMoney($val) }})</span>
                                    @endif
                                </td>
                                <td class="pr-4 text-right">
                                    @if($tab_2['valor_projetado'] >= 0)
                                        {{ \App\Utils\MyTextFormater::toMoney($tab_2['valor_projetado'] ?? 0) }}
                                    @else
                                        @php
                                            $val = floatval($tab_2['valor_projetado'] ?? 0) * -1;
                                        @endphp
                                        <span class="text-red-500">({{ \App\Utils\MyTextFormater::toMoney($val) }})</span>
                                    @endif
                                </td>
                            </tr>
                            @php
                                $contas_3 = \App\Models\PlanoDeConta::query()->where('plano_de_conta_id', $tab_2['id'])->get()->toArray();
                            @endphp
                            @foreach($contas_3 as $tab_3)
                                <tr class="bg-gray-200 font-light  cursor-pointer"
                                    x-on:click="window.location.href = '{{ route('filament.app.financeiro.resources.plano-de-contas.edit', [$tab_3['id']]) }}'">
                                    <td colspan="4" class="pl-4 text-right">
                                        {{ $tab_3['codigo'] }}
                                    </td>
                                    <td colspan="2">
                                        {{ $tab_3['descricao'] }}
                                    </td>
                                    <td class="text-right pr-4 border-right border-gray-200">
                                        @if($tab_3['valor_realizado'] >= 0)
                                            {{ \App\Utils\MyTextFormater::toMoney($tab_3['valor_realizado'] ?? 0) }}
                                        @else
                                            @php
                                                $val = floatval($tab_3['valor_realizado'] ?? 0) * -1;
                                            @endphp
                                            <span class="text-red-500">({{ \App\Utils\MyTextFormater::toMoney($val) }})</span>
                                        @endif
                                    </td>
                                    <td class="pr-4 text-right">
                                        @if($tab_3['valor_projetado'] >= 0)
                                            {{ \App\Utils\MyTextFormater::toMoney($tab_3['valor_projetado'] ?? 0) }}
                                        @else
                                            @php
                                                $val = floatval($tab_3['valor_projetado'] ?? 0) * -1;
                                            @endphp
                                            <span class="text-red-500">({{ \App\Utils\MyTextFormater::toMoney($val) }})</span>
                                        @endif
                                    </td>
                                </tr>
                                @php
                                    $contas_4 = \App\Models\PlanoDeConta::query()->where('plano_de_conta_id', $tab_3['id'])->get()->toArray();
                                @endphp
                            @endforeach
                        @endforeach
                    @endforeach
                @endforeach
                <tr>
                    <td>&nbsp;</td>
                </tr>
                </tbody>
            </table>
            <section>
    @endif
</x-filament-panels::page>
