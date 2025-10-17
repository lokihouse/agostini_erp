@props(['account', 'level' => 0, 'monthStrings' => []])

@php
    $padding = 13 + ($level * 10);
    $isParent = $account->childAccounts->isNotEmpty();
@endphp

<tr class="@if($level===0) font-bold bg-white @elseif($level===1) font-medium bg-gray-50 @else text-gray-600 @endif">
    {{-- Plano de Contas --}}
    <td class="px-2 py-2" style="padding-left: {{ $padding }}px">
        {{ $account->code }} — {{ $account->name }}
    </td>

    {{-- Coluna Meta (só conta pai pode editar) --}}
   <td class="p-2 text-center">
        @if($isParent)
            <input type="text"
                class="w-20 text-right border rounded px-1 py-0.5 text-sm"
                value="{{ $this->cashFlowsMap[$account->uuid]['goal'] ?? '' }}"
                wire:change="updateMetaCell('{{ $account->uuid }}', $event.target.value)">
        @else
            <span class="text-gray-400">-</span>
        @endif
    </td>

    {{-- Colunas dos meses --}}
    @foreach($monthStrings as $month)
        @php 
            $val = $this->getCellValue($account->uuid, $month);

            if ($isParent) {
                $val = $this->getParentSum($account, $month);
            }
        @endphp

        <td class="p-2 text-right">
            @if($isParent)
                {{-- Conta pai: só exibe soma --}}
                <span class="font-semibold text-gray-700">
                    {{ is_null($val) ? '' : number_format($val, 2, ',', '.') }}
                </span>
            @else
                {{-- Conta filha: input editável --}}
                <input type="text"
                    class="w-20 text-right border rounded px-1 py-0.5 text-sm"
                    value="{{ is_null($val) ? '' : number_format($val, 2, ',', '.') }}"
                    wire:change="updateCell('{{ $account->uuid }}', '{{ $month }}', $event.target.value)">
            @endif
        </td>
    @endforeach
</tr>

{{-- filhos recursivos --}}
@foreach($account->childAccounts as $child)
    @include('filament.partials.cash-flow-row', [
        'account' => $child,
        'level' => $level + 1,
        'monthStrings' => $monthStrings
    ])
@endforeach
