{{-- resources/views/filament/tables/columns/progress-bar.blade.php --}}
@php
    // Acessa o registro atual dentro da view da coluna
    $record = $getRecord();

    $planned = $record->items_sum_quantity_planned ?? 0;
    $produced = $record->items_sum_quantity_produced ?? 0;
    $percentage = null;
    $bgColor = 'bg-gray-200 dark:bg-gray-700';
    $barColor = 'bg-primary-500';

    if ($planned > 0) {
        $percentage = min(100, max(0, round(($produced / $planned) * 100)));
    }
@endphp

{{-- Renderiza a barra apenas se a porcentagem foi calculada --}}
@if (!is_null($percentage))
    <div class="w-full {{ $bgColor }} rounded-full h-2.5 overflow-hidden"
         role="progressbar"
         aria-valuenow="{{ $percentage }}"
         aria-valuemin="0"
         aria-valuemax="100"
         aria-label="Progresso de {{ $record->name ?? 'item' }}"> {{-- Or a more descriptive label --}}
        <div class="{{ $barColor }} h-2.5 rounded-full text-center text-xs font-medium text-white leading-none" style="width: {{ $percentage }}%">
            {{ $percentage }}%
        </div>
    </div>
@endif

