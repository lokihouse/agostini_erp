{{-- resources/views/pdf/financial_report.blade.php --}}
    <!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório Financeiro</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #374151; }
        img{width: 150px;}
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 24px; margin: 0; }
        .header p { font-size: 12px; margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; border: 1px solid #e5e7eb; }
        thead th { background: #f3f4f6; text-transform: uppercase; font-size: 11px; color: #374151; }
        tr.root { font-weight: bold; background: #fff; color: #111827; }
        tr.first { font-weight: 500; background: #fff; color: #374151; }
        tr.first td:first-of-type { width: 20px; }
        tr.second { font-weight: 400; background: #fff; color: #6b7280; }
        tr.second td:first-of-type { width: 20px; }
        tr.second td:nth-of-type(2) { width: 20px; }
        tr.third { font-weight: 300; background: #fff; color: #9ca3af; }
        tr.third td:first-of-type { width: 20px; }
        tr.third td:nth-of-type(2) { width: 20px; }
        tr.third td:nth-of-type(3) { width: 20px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-red { color: #ef4444; }
        .text-muted { color: #9ca3af; }
        .border-s { border-left: 1px solid #e5e7eb; }
        /* Adicione mais estilos conforme necessário */
    </style>
</head>
<body>
     <header>
        <div>
            <img src="images/logo-agostini-full_color-1-horizontal.png" alt="Agostini Tecnologia de Gestão">
        </div>
    </header>
<div class="header">
    <h1>Relatório de Contas Financeiras</h1>
    <p>Gerado em: {{ \Carbon\Carbon::now()->translatedFormat('d/m/y H:i') }}</p>
</div>
<table>
    <thead>
    <tr>
        <th colspan="4">Plano de Contas</th>
        @foreach($monthHeaders as $monthDate)
            <th class="text-center">{{ $monthDate->translatedFormat('M/y') }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($reportData as $item)
        <tr class="root">
            <td colspan="4">{{ $item->code }}. {{ $item->name }}</td>
            @foreach($monthHeaders as $monthDate)
                @php
                    $value = $item->getValuesForPeriod($monthDate->copy()->startOfMonth(), $monthDate->copy()->endOfMonth());
                @endphp
                <td class="text-right">
                    @if($value < 0)
                        <span class="text-red">({{ number_format(abs($value), 2, ',', '.') }})</span>
                    @elseif($value > 0)
                        {{ number_format($value, 2, ',', '.') }}
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
            @endforeach
        </tr>
        @foreach($item->childAccounts as $firstChild)
            <tr class="first">
                <td></td>
                <td colspan="3">{{ $firstChild->code }}. {{ $firstChild->name }}</td>
                @foreach($monthHeaders as $monthDate)
                    @php
                        $value = $firstChild->getValuesForPeriod($monthDate->copy()->startOfMonth(), $monthDate->copy()->endOfMonth());
                    @endphp
                    <td class="text-right">
                        @if($value < 0)
                            <span class="text-red">({{ number_format(abs($value), 2, ',', '.') }})</span>
                        @elseif($value > 0)
                            {{ number_format($value, 2, ',', '.') }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                @endforeach
            </tr>
            @foreach($firstChild->childAccounts as $secondChild)
                <tr class="second">
                    <td></td>
                    <td></td>
                    <td colspan="2">{{ $secondChild->code }}. {{ $secondChild->name }}</td>
                    @foreach($monthHeaders as $monthDate)
                        @php
                            $value = $secondChild->getValuesForPeriod($monthDate->copy()->startOfMonth(), $monthDate->copy()->endOfMonth());
                        @endphp
                        <td class="text-right">
                            @if($value < 0)
                                <span class="text-red">({{ number_format(abs($value), 2, ',', '.') }})</span>
                            @elseif($value > 0)
                                {{ number_format($value, 2, ',', '.') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
                @foreach($secondChild->childAccounts as $thirdChild)
                    <tr class="third">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>{{ $thirdChild->code }}. {{ $thirdChild->name }}</td>
                        @foreach($monthHeaders as $monthDate)
                            @php
                                $value = $thirdChild->getValuesForPeriod($monthDate->copy()->startOfMonth(), $monthDate->copy()->endOfMonth());
                            @endphp
                            <td class="text-right">
                                @if($value < 0)
                                    <span class="text-red">({{ number_format(abs($value), 2, ',', '.') }})</span>
                                @elseif($value > 0)
                                    {{ number_format($value, 2, ',', '.') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            @endforeach
        @endforeach
    @endforeach
    </tbody>
</table>
</body>
</html>
