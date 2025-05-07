<div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
        Olá, {{ $userName }}!
    </h4>

    <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
        @if($workShiftName)
            <p>Sua jornada: <strong class="font-medium text-gray-900 dark:text-white">{{ $workShiftName }}</strong></p>
        @else
            <p>Nenhuma jornada de trabalho atribuída.</p>
        @endif

        <p>Horas programadas para hoje: <strong class="font-medium text-gray-900 dark:text-white">{{ $scheduledHoursToday ?? 'N/D' }}</strong></p>
        <p>Horas trabalhadas hoje: <strong class="font-medium text-gray-900 dark:text-white">{{ $workedHoursToday ?? '00:00' }}</strong></p>
    </div>

    <div class="mt-6">
        {{-- Botão agora é dinâmico --}}
        <button wire:click="redirectToRegisterPointMap('{{ $nextActionType }}')"
                class="w-full px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:ring-4 focus:ring-primary-300 focus:outline-none dark:bg-primary-500 dark:hover:bg-primary-600 dark:focus:ring-primary-800">
            {{ $nextActionLabel }}
        </button>

        {{-- Se o usuário já bateu entrada e não saiu, pode ser útil ter um botão de saída rápido também --}}
        {{-- Ou se a próxima ação é "Iniciar Pausa", também poderia ter "Bater Saída" --}}
        @if($hasActiveSession && $nextActionType === 'start_break')
            <button wire:click="redirectToRegisterPointMap('clock_out')"
                    class="w-full mt-2 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-300 focus:outline-none dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-800">
                Bater Saída Direto
            </button>
        @endif
    </div>

    {{-- Comentário sobre a última batida pode ser útil para o usuário --}}
    @php
        $lastEntryForDisplay = \App\Models\TimeClockEntry::where('user_id', Auth::id())
            ->whereDate('recorded_at', \Carbon\Carbon::today())
            ->orderBy('recorded_at', 'desc')
            ->first();
    @endphp
    @if($lastEntryForDisplay)
        <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
            Última batida: {{ $lastEntryForDisplay->entry_type_label }} às {{ $lastEntryForDisplay->recorded_at->format('H:i') }}
        </p>
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
