<x-filament::section compact collapsible collapsed>
    <x-slot name="heading">
        Relatório de Visita
    </x-slot>

    <div class='font-bold'>Descrição detalhada da visita:</div>
    <div>
        {!! json_decode($this->relatorio, true)['descricao'] !!}
    </div>
    <div class='font-bold'>Plano de Ações:</div>
    <div class="relative overflow-x-auto text-xs text-left">
        <table class="w-full text-gray-500 dark:text-gray-400">
            <thead class="text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">
                    O que fazer?
                </th>
                <th scope="col" class="px-6 py-3 w-[200px]">
                    Quem?
                </th>
                <th scope="col" class="px-6 py-3 w-[120px]">
                    Quando?
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach((json_decode($this->relatorio, true))['plano_de_acoes'] as $plano_de_acao_item)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                    <td class="px-6 py-4">
                        {!! $plano_de_acao_item['o_que_fazer'] !!}
                    </td>
                    <td class="px-6 py-4">
                        {!! $plano_de_acao_item['quem'] !!}
                    </td>
                    <td class="px-6 py-4">
                        {!! \Carbon\Carbon::parse($plano_de_acao_item['prazo'])->translatedFormat('d/m/Y') !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</x-filament::section>
