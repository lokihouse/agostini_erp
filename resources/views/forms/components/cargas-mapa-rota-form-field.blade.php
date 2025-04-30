@php
$state = $getState();
@endphp
<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{
        state: $wire.$entangle('{{ $getStatePath() }}'),
    }">
        @livewire('google-maps', [])
    </div>
</x-dynamic-component>
