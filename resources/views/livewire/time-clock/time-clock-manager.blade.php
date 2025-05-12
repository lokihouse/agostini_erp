<div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="fi-section-header-ctn border-b border-gray-200 px-6 py-4 dark:border-white/10">
        <div class="fi-section-header flex flex-col gap-y-2 sm:flex-row sm:items-center">
            <div class="grid flex-1 gap-y-1">
                <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                    Olá, {{ $userName }}
                </h3>
                <p class="fi-section-header-description text-sm text-gray-500 dark:text-gray-400">
                    Controle aqui seus registros de ponto
                </p>
            </div>
        </div>
    </div>

    <div class="p-4 space-y-2">
        <x-filament::button
            tag="a"
            href="{{ route('filament.app.pages.registro-de-ponto.cartao-de-ponto') }}"
            outlined
            class="w-full">
            Meu cartão de ponto
        </x-filament::button>

        {{-- Botão agora é dinâmico --}}
        <button wire:click="redirectToRegisterPointMap('{{ $nextActionType }}')"
                class="w-full px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:ring-4 focus:ring-primary-300 focus:outline-none dark:bg-primary-500 dark:hover:bg-primary-600 dark:focus:ring-primary-800">
{{--            {{ $nextActionLabel }}--}}
            Bater ponto
        </button>

        {{-- Se o usuário já bateu entrada e não saiu, pode ser útil ter um botão de saída rápido também --}}
        {{-- Ou se a próxima ação é "Iniciar Pausa", também poderia ter "Bater Saída" --}}
{{--        @if($hasActiveSession && $nextActionType === 'start_break')--}}
{{--            <button wire:click="redirectToRegisterPointMap('clock_out')"--}}
{{--                    class="w-full mt-2 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-300 focus:outline-none dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-800">--}}
{{--                Bater Saída Direto--}}
{{--            </button>--}}
{{--        @endif--}}
    </div>

    {{-- Comentário sobre a última batida pode ser útil para o usuário --}}
    @php
        $lastEntryForDisplay = \App\Models\TimeClockEntry::where('user_id', Auth::id())
            ->whereDate('recorded_at', \Carbon\Carbon::today())
            ->orderBy('recorded_at', 'desc')
            ->first();
    @endphp
    @if($lastEntryForDisplay)
{{--        <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">--}}
{{--            Última batida: {{ $lastEntryForDisplay->entry_type_label }} às {{ $lastEntryForDisplay->recorded_at->format('H:i') }}--}}
{{--        </p>--}}
    @endif


    @if (session()->has('message'))
        <div class="mt-3 p-3 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800" role="alert">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mt-3 p-3 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
            {{ session('error') }}
        </div>
    @endif
</div>
