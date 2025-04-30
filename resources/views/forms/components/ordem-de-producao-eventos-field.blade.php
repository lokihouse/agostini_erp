@php
    $state = $getState() ?? [];
    usort($state, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
    class="-m-4"
>
    <div x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }">
        <div class="rounded-b-xl bg-gray-100 dark:border-gray-700 border-gray-200 px-4">

            @foreach($state as $evento)
            <div class="flex items-start gap-2 border-b py-2">
                <div class="flex justify-center items-center align-middle h-10 w-10">
                    <x-filament-panels::avatar.user :user="\App\Models\User::query()->where('id', $evento['user_id'])->first()" />
                </div>
                <div class="flex flex-col w-full leading-1.5">
                    <div class="flex flex-col items-start rtl:space-x-reverse">
                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{$evento['responsavel']['nome']}}</div>
                        <div class="text-sm font-normal text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($evento['created_at'])->translatedFormat('d/m/Y H:i') }}</div>
                    </div>
                    <p class="text-sm font-normal text-gray-900 dark:text-white"><b>{{ $evento['nome'] }}</b></p>
                    @if($evento['descricao'])
                        <p class="text-sm font-normal text-gray-900 dark:text-white">{{ $evento['descricao'] }}</p>
                    @endif
                </div>
            </div>
            @endforeach

            <pre>{{ json_encode($state, 128) }}</pre>
        </div>
    </div>
</x-dynamic-component>
