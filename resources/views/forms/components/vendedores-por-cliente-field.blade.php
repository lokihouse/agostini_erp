<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }">
        @livewire(\App\Livewire\VendedoresPorCliente::class, ['cliente_id' => $getRecord()->id])
    </div>
</x-dynamic-component>
