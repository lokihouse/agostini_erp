@php
    use Carbon\Carbon;

    $color_orange = Filament\Support\Colors\Color::Orange;
    $color_blue = Filament\Support\Colors\Color::Blue;
    $color_emerald = Filament\Support\Colors\Color::Emerald;
    $color_red = Filament\Support\Colors\Color::Red;

    $ordensDeProducao = \App\Models\OrdemDeProducao::query()
        ->whereIn('status', ['em_producao'])
        ->orderBy('status')
        ->orderBy('data_inicio')
        ->get()
        ->toArray();

    $ordensDeProducao_dates = array_reduce($ordensDeProducao, function($carry, $item) {
        $date = new Carbon($item['data_inicio']);
        if(is_null($carry[0]) || $date->lt($carry[0])) $carry[0] = $date;

        $date = max([new Carbon($item['data_previsao']), new Carbon($item['data_final'])]);
        if(is_null($carry[1]) || $date->gt($carry[1])) $carry[1] = $date;
        return $carry;
    }, [null, null]);

    $ordensDeProducao_dates[0] = $ordensDeProducao_dates[0]->subDays(5)->format('U') * 1000;
    $ordensDeProducao_dates[1] = $ordensDeProducao_dates[1]->addDays(5)->format('U') * 1000;

    // dd($ordensDeProducao_dates);
@endphp
<x-filament-panels::page>
    <style>
        #container {
            margin: 1em auto;
        }

        .scrolling-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    </style>

    <x-filament::section compact>
        <x-slot name="heading">
            Ordens de Produção
        </x-slot>

        <div class="scrolling-container">
            <div id="container"></div>
        </div>
    </x-filament::section>


    @pushonce('scripts')
        <script src="https://code.highcharts.com/gantt/highcharts-gantt.js"></script>
        <script src="https://code.highcharts.com/gantt/modules/exporting.js"></script>
        <script src="https://code.highcharts.com/gantt/modules/accessibility.js"></script>
        <script>
            var ordensDeProducaoMapped = @json($ordensDeProducao).map((ordem => {
                let opacity = 1;
                let color = 'rgba({{ $color_blue[500] }}, 0.75)';

                var endDate = [];
                endDate.push(new Date(ordem.data_previsao).getTime())
                endDate.push(new Date(ordem.data_final).getTime())
                endDate = Math.max(...endDate)

                return {
                    name: ordem.codigo,
                    id: ordem.codigo,
                    start: new Date(ordem.data_inicio).getTime(),
                    end: endDate,
                    completed: {amount: ordem.percentual_concluido},
                    status: ordem.status,
                    opacity,
                    color,
                }
            }))

            console.log(ordensDeProducaoMapped);

            if (ordensDeProducaoMapped.length !== 0) {
                var options = {
                    chart: {
                        marginRight: 50,
                    },
                    plotOptions: {
                        gantt: {},
                        series: {
                            groupPadding: 0,
                            dataLabels: [
                                {
                                    enabled: true,
                                    align: 'left',
                                    format: '{point.status}',
                                    padding: 10,
                                    style: {
                                        color: 'white',
                                        fontFamily: 'monospace',
                                    }
                                }, {
                                    enabled: true,
                                    align: 'right',
                                    format: '{#if point.completed}{(multiply ' +
                                        'point.completed.amount 100):.0f}%{/if}',
                                    padding: 10,
                                    style: {
                                        color: 'white',
                                        fontFamily: 'monospace',
                                    }
                                }
                            ]
                        }
                    },
                    credits: {
                        enabled: false,
                    },
                    series: [
                        {
                            name: 'OrdensDeProducao',
                            data: ordensDeProducaoMapped,
                        }
                    ],
                    tooltip: {
                        enabled: false,
                    },
                    xAxis: [{
                        currentDateIndicator: {
                            color: '#ff0000',
                            width: 1,
                            label: {
                                format: '',
                            }
                        },
                        dateTimeLabelFormats: {
                            week: '<span style="opacity: 0.5;">Semana<br/>%e %B</span>',
                            day: '<span style="opacity: 0.5; font-size: 0.7em">%d<br/>%b</span>'
                        },
                        startOnTick: true,
                        endOnTick: true,
                        grid: {
                            borderWidth: 1,
                            cellHeight: 50,
                            borderColor: "#e6e6e6"
                        },
                        gridLineWidth: 1,
                        min: {{ max($ordensDeProducao_dates[0], Carbon::today()->subDays(2)->format('U') * 1000) }},
                        max: {{ max($ordensDeProducao_dates[1], Carbon::today()->addDays(31)->format('U') * 1000) }},
                        custom: {
                            weekendPlotBands: true
                        }
                    }],
                    yAxis: {
                        grid: {
                            borderWidth: 0
                        },
                        gridLineWidth: 0,
                    },
                    navigator: {
                        enabled: true,
                        liveRedraw: true,
                    },
                };
                Highcharts.addEvent(Highcharts.Axis, 'foundWeekends', e => {
                    if (e.target.options.custom && e.target.options.custom.weekendPlotBands) {
                        const axis = e.target,
                            chart = axis.chart,
                            day = 24 * 36e5,
                            isWeekend = t => /[06]/.test(chart.time.dateFormat('%w', t)),
                            plotBands = [];

                        let inWeekend = false;

                        for (
                            let x = Math.floor(axis.min / day) * day;
                            x <= Math.ceil(axis.max / day) * day;
                            x += day
                        ) {
                            const last = plotBands.at(-1);
                            if (isWeekend(x) && !inWeekend) {
                                plotBands.push({
                                    from: x,
                                    color: 'rgba(255,0,0,0.1)'
                                });
                                inWeekend = true;
                            }

                            if (!isWeekend(x) && inWeekend && last) {
                                last.to = x;
                                inWeekend = false;
                            }
                        }
                        axis.options.plotBands = plotBands;
                    }
                });
                Highcharts.ganttChart('container', options);
            }
        </script>
    @endpushonce
</x-filament-panels::page>
