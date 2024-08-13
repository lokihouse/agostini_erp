@php
    use Illuminate\Support\Str;
    $uuid = str_replace("-", "", Str::uuid());
@endphp
<div>
    <div id="{{ $uuid }}"></div>
</div>

@script
<script>
    const id = @js($uuid);
    const options = {
        chart: {
            plotBackgroundColor: 'rgba(128,128,128,0.02)',
            plotBorderColor: 'rgba(128,128,128,0.1)',
            plotBorderWidth: 1,
        },
        title: false,
        credits: false,
        navigator: {
            enabled: true,
            liveRedraw: true,
            series: {
                type: 'gantt',
                pointPlacement: 0.5,
                pointPadding: 0.25,
                accessibility: {
                    enabled: false
                }
            },
            yAxis: {
                min: 0,
                max: 3,
                reversed: true,
                categories: []
            }
        },
        scrollbar: {
            enabled: true
        },
        accessibility: {
            point: {
                descriptionFormat: 'Start {x:%Y-%m-%d}, end {x2:%Y-%m-%d}.'
            },
            series: {
                descriptionFormat: '{name}'
            }
        },
        tooltip: {
            formatter: function() {
                return '<b>' + this.point.name + '</b><br/>' +
                    'Início: ' + Highcharts.dateFormat('%d/%m/%Y', this.point.start) + '<br/>' +
                    'Final: ' + Highcharts.dateFormat('%d/%m/%Y', this.point.end) + '<br/>' +
                    '';
            },
        },
        series: [{
            data: @json($series)
        }],

        xAxis: [{
            currentDateIndicator: {
                color: '#2caffe',
                dashStyle: 'ShortDot',
                width: 2,
                label: false
            },
            dateTimeLabelFormats: {
                day: '%d<br><span style="opacity: 0.5; font-size: 0.7em">%a</span>'
            },
            grid: {
                borderWidth: 1
            },
            gridLineWidth: 1,
        }],
        yAxis: {
            grid: {
                borderWidth: 1,
            },
            gridLineWidth: 1,
            labels: {
                symbol: {
                    width: 8,
                    height: 6,
                    x: -4,
                    y: -2
                }
            },
            staticScale: 30
        },
    };



    Highcharts.ganttChart(id, options);
</script>
@endscript
