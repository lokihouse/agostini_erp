@php
    use Filament\Support\Facades\FilamentColor;

@endphp
<x-filament-panels::page>
    @livewire(\App\Livewire\ProducaoCronogramaWidget::class)
    <div style="margin: 0 auto">
        <div class="flex gap-x-2 text-xs">
            <div class="flex gap-x-2">
                <div style="background: rgb({{ FilamentColor::getColors()['warning']['500'] }}); width: 20px !important;"></div> - Ordens de produção agendadas
            </div>
            <div class="flex gap-x-2">
                <div style="background: rgb({{ FilamentColor::getColors()['info']['500'] }}); width: 20px !important;"></div> - Ordens de produção em produção
            </div>
            <div class="flex gap-x-2">
                <div style="background: rgb({{ FilamentColor::getColors()['success']['500'] }}); width: 20px !important;"></div> - Ordens de produção finalizada
            </div>
            <div class="flex gap-x-2">
                <div style="background: rgb({{ FilamentColor::getColors()['danger']['500'] }}); width: 20px !important;"></div> - Ordens de produção cancelada
            </div>
        </div>
    </div>
</x-filament-panels::page>
