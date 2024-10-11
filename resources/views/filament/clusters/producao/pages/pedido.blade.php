@php
    use App\Models\OrdemDeProducao;
    use App\Models\Produto;

    $ordem = $ordem ?? $this->getRecord();

    $hasBarCode = $ordem->status === "em_producao";
    $barcodePattern = 'C128';

    $ordemDeProducao = OrdemDeProducao::query()->find($ordem['id']);

    function formatStatus($status) {
        return match($status) {
            'agendada' => 'Agendada',
            'em_producao' => 'Em Produção',
            default => $status,
        };
    }
@endphp
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>ORDEM DE PRODUCAO {{$ordemDeProducao->id}}</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
    * {
        padding: 0;
        margin: 0;
    }
</style>
<div class="w-full flex-1 flex-col flex bg-white">
    <div class="space-y-4">
        <nav class="flex h-16 items-center gap-x-4 px-4">
            <div class="me-6">
                <div class="flex text-2xl font-bold leading-5 tracking-tight text-gray-950">
                    AGOSTINI
                </div>
                <div class="text-left">
                    <div class="text-sm font-bold leading-5 tracking-tight text-gray-950">ORDEM DE PRODUÇÃO</div>
                    <div class="text-xs font-bold text-gray-950 uppercase">#{{$ordem['id']}} - {{ formatStatus($ordem['status']) }}</div>
                </div>
            </div>

            <div class="ms-auto flex items-center gap-x-4">
                <div>
                    @if($hasBarCode)
                    <img
                        src="data:image/png;base64,{{ Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG("" . $ordem['id'], $barcodePattern) }}"
                        alt="barcode"/>
                    @else
                        <div class="text-right">
                            <span class="font-black">ORDEM DE PRODUÇÃO AGENDADA</span><br/>
                            <span class="font-black">CARGA DE PRODUÇÃO NÃO INICIADA</span>
                        </div>
                    @endif
                </div>
            </div>
        </nav>

        <div class="px-4 space-y-4">
            <section class="rounded bg-white shadow-sm ring-1 ring-gray-950/5">
                <header class="bg-gray-200 flex flex-col gap-2 px-2 py-2">
                    <div class="flex items-center gap-3">
                        <svg class="fi-section-header-icon self-start text-gray-400 dark:text-gray-500 fi-color-{$iconColor} h-6 w-6" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--! Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2023 Fonticons, Inc. --><path d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zm64 80v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm128 0v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H208c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H336zM64 400v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H208zm112 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H336c-8.8 0-16 7.2-16 16z"></path></svg>
                        <div class="grid flex-1 gap-y-1">
                            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                                Cronograma
                            </h3>
                        </div>
                    </div>
                </header>

                <div class="border-t border-gray-200">
                    <div class="p-2">
                        <div class="grid grid-cols-4 gap-x-2">
                            <div>
                                <fieldset class="rounded border border-gray-200 p-2 dark:border-white/10">
                                    <legend class="px-2 text-xs font-medium leading-6 text-gray-500 dark:text-white">
                                        Agendamento
                                    </legend>
                                    {{ \Carbon\Carbon::parse($ordem['data_inicio_agendamento'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($ordem['data_final_agendamento'])->format('d/m/Y') }}
                                </fieldset>
                            </div>
                            <div>
                                <fieldset class="rounded border border-gray-200 p-2 dark:border-white/10">
                                    <legend class="px-2 text-xs font-medium leading-6 text-gray-950 dark:text-white">
                                        Produção
                                    </legend>
                                    @if($ordem['status'] === 'agendada')
                                        <span class="">Não iniciada</span>
                                    @else
                                        {{ \Carbon\Carbon::parse($ordem['data_inicio_producao'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($ordem['data_final_producao'])->format('d/m/Y') }}
                                    @endif
                                </fieldset>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded bg-white shadow-sm ring-1 ring-gray-950/5">
                <header class="bg-gray-200 flex flex-col gap-2 px-2 py-2">
                    <div class="flex items-center gap-3">
                        <svg class="fi-section-header-icon self-start text-gray-400 dark:text-gray-500 fi-color-{$iconColor} h-6 w-6" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--! Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2023 Fonticons, Inc. --><path d="M0 80C0 53.5 21.5 32 48 32h96c26.5 0 48 21.5 48 48V96H384V80c0-26.5 21.5-48 48-48h96c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48H432c-26.5 0-48-21.5-48-48V160H192v16c0 1.7-.1 3.4-.3 5L272 288h96c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48H272c-26.5 0-48-21.5-48-48V336c0-1.7 .1-3.4 .3-5L144 224H48c-26.5 0-48-21.5-48-48V80z"></path></svg>
                        <div class="grid flex-1 gap-y-1">
                            <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                                Produção
                            </h3>
                        </div>
                    </div>
                </header>

                <div class="border-t border-gray-200">
                    <div class="p-2">
                        <div class="grid grid-cols-1 gap-x-2">
                            <div class="flex flex-col gap-y-2">
                                <table
                                    class="w-full rounded bg-white shadow-sm ring-1 ring-gray-950/5 table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                                    <thead class="divide-y divide-gray-200 dark:divide-white/5">
                                        <tr class="bg-gray-100 dark:bg-white/5">
                                            <th colspan="2" class="p-2 w-1">
                                                <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                                    <span
                                                        class="text-xs font-bold text-gray-500 dark:text-white">
                                                        Itens para Produção
                                                    </span>
                                                </span>
                                            </th>
                                        </tr>
                                        <tr class="bg-gray-50 dark:bg-white/5">
                                            <th class="p-2 w-1">
                                                <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                                    <span
                                                        class="text-xs font-bold text-gray-500 dark:text-white">
                                                        Quant.
                                                    </span>
                                                </span>
                                            </th>

                                            <th class="p-2">
                                                <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                                    <span
                                                        class="text-xs font-bold text-gray-500 dark:text-white">
                                                        Item
                                                    </span>
                                                </span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                                        @foreach(json_decode($ordemDeProducao->produtos) as $produto)
                                        <tr class="">
                                            <td class="p-0">
                                                <div class="flex w-full justify-start text-start">
                                                    <div class="grid w-full p-2">
                                                        <div class="flex ">
                                                            <div class="flex max-w-max">
                                                                <div class="inline-flex items-center gap-1.5  ">
                                                                    <span class="text-xs leading-6 text-gray-950 dark:text-white  ">
                                                                        {{ $produto->quantidade }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="p-0">
                                                <div class="flex w-full justify-start text-start">
                                                    <div class="grid w-full p-2">
                                                        <div class="flex ">
                                                            <div class="flex max-w-max">
                                                                <div class="inline-flex items-center gap-1.5  ">
                                                                    <span class="text-xs leading-6 text-gray-950 dark:text-white  ">
                                                                        {{ $produto->nome ?? '-' }}
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
                                <table
                                    class="w-full rounded bg-white shadow-sm ring-1 ring-gray-950/5 table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                                    <thead class="divide-y divide-gray-200 dark:divide-white/5">
                                    <tr class="bg-gray-100 dark:bg-white/5">
                                        <th colspan="7" class="p-2 w-1">
                                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                                <span
                                                    class="text-xs font-bold text-gray-500 dark:text-white">
                                                    Cargas
                                                </span>
                                            </span>
                                        </th>
                                    </tr>
                                    <tr class="bg-gray-50 dark:bg-white/5">
                                        <th class="p-2 w-1">
                                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-center">
                                                <span
                                                    class="text-xs font-bold text-gray-500 dark:text-white">
                                                    Quant.
                                                </span>
                                            </span>
                                        </th>

                                        <th class="p-2 w-1/4">
                                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                                <span
                                                    class="text-xs font-bold text-gray-500 dark:text-white">
                                                    Produto
                                                </span>
                                            </span>
                                        </th>

                                        <th class="p-2">
                                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                                <span
                                                    class="text-xs font-bold text-gray-500 dark:text-white">
                                                    Descrição
                                                </span>
                                            </span>
                                        </th>

                                        <th class="p-2 w-1">
                                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-center">
                                                <span
                                                    class="text-xs font-bold text-gray-500 dark:text-white">
                                                    Larg.¹
                                                </span>
                                            </span>
                                        </th>

                                        <th class="p-2 w-1">
                                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-center">
                                                <span
                                                    class="text-xs font-bold text-gray-500 dark:text-white">
                                                    Altu.¹
                                                </span>
                                            </span>
                                        </th>

                                        <th class="p-2 w-1">
                                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-center">
                                                <span
                                                    class="text-xs font-bold text-gray-500 dark:text-white">
                                                    Comp.¹
                                                </span>
                                            </span>
                                        </th>

                                        <th class="p-2 w-1">
                                            <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-center">
                                                <span
                                                    class="text-xs font-bold text-gray-500 dark:text-white">
                                                    Peso²
                                                </span>
                                            </span>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                                    @foreach(json_decode($ordemDeProducao->produtos) as $produto)
                                        @php
                                            $volumes = json_decode(Produto::query()->find($produto->produto_id)->volumes);
                                        @endphp
                                        @foreach($volumes as $volume)
                                            <tr class="">
                                                <td class="p-0 text-center text-xs leading-6 text-gray-950 dark:text-white  ">
                                                    {{ intval($produto->quantidade) * intval($volume->quantidade) }}
                                                </td>

                                                <td class="p-2 text-xs leading-6 text-gray-950 dark:text-white  ">
                                                    {{ $produto->nome }}
                                                </td>

                                                <td class="p-2 text-xs leading-6 text-gray-950 dark:text-white  ">
                                                    {{ $volume->descricao }}
                                                </td>

                                                <td class="p-2 text-xs text-center leading-6 text-gray-950 dark:text-white  ">
                                                    {{ $volume->largura }}
                                                </td>

                                                <td class="p-2 text-center text-xs leading-6 text-gray-950 dark:text-white  ">
                                                    {{ $volume->altura }}
                                                </td>

                                                <td class="p-2 text-center text-xs leading-6 text-gray-950 dark:text-white  ">
                                                    {{ $volume->comprimento }}
                                                </td>

                                                <td class="p-2 text-center text-xs leading-6 text-gray-950 dark:text-white  ">
                                                    {{ $volume->peso }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                    </tbody>
                                </table>
                                <div style="font-size: 8px" class="text-gray-400">
                                    <div>1 - Medidas em centímetros</div>
                                    <div>2 - Medidas em gramas</div>
                                </div>
                            </div>
                            <div class="flex mb-8">
                                <div class="w-full rounded bg-white shadow-sm ring-1 ring-gray-950/5 table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                                    <div class="divide-y divide-gray-200 dark:divide-white/5">
                                        <div class="bg-gray-100 dark:bg-white/5">
                                            <div colspan="2" class="p-2 w-1">
                                                <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                                    <span
                                                        class="text-xs font-bold text-gray-500 dark:text-white">
                                                        Mapa de Produção
                                                    </span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-2">
                                        @if($hasBarCode)
                                            <img
                                                src="{{ $ordemDeProducao->mapa_de_processo }}"
                                                alt="mapa_de_processo"/>
                                        @else
                                            <div class="flex pt-8 justify-center items-center">
                                                <div class="text-center">
                                                    <span class="font-black">ORDEM DE PRODUÇÃO AGENDADA</span><br/>
                                                    <span class="font-black">MAPA DE PRODUÇÃO NÃO CONFIRMADO</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        @pageBreak
        <div class="grid grid-cols-1 gap-y-2">
            @php
            $itensCount = 0;
            $itensCountLimit = 6;
            @endphp
            @foreach(json_decode($ordemDeProducao->produtos) as $produto)
                @foreach($produto->etapas as $idx => $etapa)
                    <div class="h-[50mm] flex items-stretch px-4" @class([" pt-[4mm]" => $itensCount === 0 ])>
                        <section class="rounded w-full bg-white shadow-sm ring-1 ring-gray-950/5">
                            <header class="bg-gray-200 flex flex-col gap-2 px-2 py-2">
                                <div class="flex items-center gap-3">
                                    <svg class="self-start text-gray-400 dark:text-gray-500 fi-color-{$iconColor} h-6 w-6" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--! Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2023 Fonticons, Inc. --><path d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zm64 80v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm128 0v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H208c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H336zM64 400v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H208zm112 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H336c-8.8 0-16 7.2-16 16z"></path></svg>
                                    <div class="flex-1 flex items-center gap-y-1">
                                        <div class="flex-1 text-base font-semibold leading-6 text-gray-950 dark:text-white">
                                            Etapa de Produção {{$ordemDeProducao->id}}.{{$produto->produto_id}}.{{$idx}}
                                        </div>
                                        <img
                                            src="data:image/png;base64,{{ Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($ordemDeProducao->id . "." . $produto->produto_id . "." . $idx, $barcodePattern) }}"
                                            alt="barcode"/>
                                    </div>
                                </div>
                            </header>
                        </section>
                    </div>
                    <div class="border-b-[1px] border-dashed border-red-500 w-full"></div>
                    @php
                        $itensCount++;
                        if($itensCount > $itensCountLimit):
                            $itensCount = 0;
                    @endphp
                    @pageBreak
                    @php
                        endif;
                    @endphp
                @endforeach
            @endforeach
        </div>
    </div>
</div>
