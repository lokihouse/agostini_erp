<x-filament-panels::page>
    <header class='fi-header flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between'>
        <div>
            <h1 class="fi-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
                Relatório de Vendas por Cliente
            </h1>
            <p class="fi-header-subheading mt-2 max-w-2xl text-lg text-gray-600 dark:text-gray-400">
                Período de {{ $this->startDate->translatedFormat('d/m/y') }} a {{ $this->endDate->translatedFormat('d/m/y') }}
            </p>
        </div>

        <div class='flex shrink-0 items-center gap-3'>
            <div class="flex w-full space-x-2">
{{--                {{ $this->previousMonthAction() }}--}}
{{--                {{ $this->nextMonthAction() }}--}}
            </div>
        </div>
    </header>

    {{ $this->table }}

</x-filament-panels::page>
