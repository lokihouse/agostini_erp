<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
    class="-m-4"
>
    <div x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }">
        <div class="relative overflow-x-auto  rounded-b-xl">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 rounded-xl">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="w-1">&nbsp;</th>
                    <th scope="col" class="px-2 py-3 w-1">
                        Qnt.
                    </th>
                    <th scope="col" class="px-2 py-3">
                        Produto
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach($getState() as $produto)
                    @livewire('ordem-de-producao-produtos-field-item', ['produto' => $produto])
                @endforeach
                </tbody>
            </table>
        </div>

    </div>
    <x-filament-actions::modals />
</x-dynamic-component>
