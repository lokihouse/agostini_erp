@php
    use Carbon\Carbon;$ordens_de_producao = \App\Models\OrdemDeProducao::all();

    function colorByStatus($status){
        return match ($status) {
            'agendada' => \App\Utils\MyColorsHelper::getDefaultColors('warning', format: 'hex'),
            'cancelada' => \App\Utils\MyColorsHelper::getDefaultColors('danger', format: 'hex'),
            'em_producao' => \App\Utils\MyColorsHelper::getDefaultColors('info', format: 'hex'),
            'finalizada' => \App\Utils\MyColorsHelper::getDefaultColors('success', format: 'hex'),
            default => \App\Utils\MyColorsHelper::getDefaultColors('gray', format: 'hex'),
        };
    }

    $ordens_por_status = $ordens_de_producao->sortBy('status')->groupBy('status')->map->count()->toArray();
    $ordens_por_status = array_map(function($k, $i) {
        return ['name' => $k, 'y' => $i, 'color' => colorByStatus($k)];
    }, array_keys($ordens_por_status), $ordens_por_status);

    $ordens_gantt = $ordens_de_producao->whereIn('status', ['agendada', 'em_producao'])->sortBy('status')->toArray();
    $ordens_gantt = array_values(array_map(function($ordem) {
        $start = $ordem['status'] === "agendada" ? $ordem['previsao_inicio'] : $ordem['data_inicio'];
        $end = $ordem['status'] === "agendada" ? $ordem['previsao_final'] : $ordem['data_final'];

        $start = Carbon::parse($start)->format('Uv');
        $end = Carbon::parse($end)->format('Uv');

        return [
            'name' => 'OP #' . $ordem['id'],
            'start' => intval($start),
            'end' => intval($end),
            'status' => $ordem['status'],
            'color' => colorByStatus($ordem['status']),
            'completed' => [
                'amount' => $ordem['completude'] / 100
            ],
        ];
    }, $ordens_gantt));

@endphp
<x-filament-panels::page>

    <div class="grid grid-cols-4 gap-2">
        <div>
            @livewire(\App\Filament\Widgets\OrdemDeProducaoHomeStatusChart::class, ['ordens' => $ordens_por_status])
        </div>

        <div class="col-span-3">
            @livewire(\App\Filament\Widgets\OrdemDeProducaoHomeGanttChart::class, ['ordens' => $ordens_gantt])
        </div>
    </div>
</x-filament-panels::page>
