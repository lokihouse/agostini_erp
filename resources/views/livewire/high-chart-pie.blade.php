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
    const chartData = @json($series);
    Highcharts.chart(id, {
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
        },
        title: false,
        subtitle: false,
        credits: false,
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        accessibility: {
            point: {
                valueSuffix: '%'
            }
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: false
                },
                showInLegend: true
            }
        },
        series: [{
            name: '',
            colorByPoint: true,
            data: chartData
        }]
    });
</script>
@endscript
