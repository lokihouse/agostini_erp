@php
$primaryColorHex = \App\Utils\MyColorsHelper::getDefaultColors(format: 'hex');
@endphp
<div class="w-full bg-gray-200 rounded-full dark:bg-gray-700">
    @if(floatval($getState()) > 0)
        <div
            class="text-xs font-medium text-blue-100 text-center p-0.5 leading-none rounded-full"
            style="width: {{ number_format($getState(), 0) }}%; background-color: {{ $primaryColorHex }}"
        >
            {{  number_format($getState(), 0) }}%
        </div>
    @else
        <div class="text-xs font-medium text-center p-0.5 leading-none rounded-full"> - </div>
    @endif
</div>
