<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Produção</title>
    <style>
        @page {
            margin: 20px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
        }
        .container {
            width: 100%;
        }
        h1 {
            font-size: 18px;
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        h2 {
            font-size: 14px;
            margin-top: 15px;
            margin-bottom: 8px;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
        }
        .header-info {
            text-align: right;
            font-size: 9px;
            margin-bottom: 15px;
            color: #555;
        }
        .stats-container {
            display: flex; /* dompdf tem suporte limitado a flex, mas pode funcionar para layout simples */
            justify-content: space-around; /* Tenta espaçar os blocos */
            flex-wrap: wrap; /* Permite quebrar linha se não couber */
            margin-bottom: 20px;
            width: 100%;
        }
        .stat-block {
            border: 1px solid #ddd;
            padding: 8px;
            margin: 5px;
            border-radius: 4px;
            background-color: #f9f9f9;
            width: 30%; /* Tenta fazer 3 colunas */
            box-sizing: border-box;
            page-break-inside: avoid;
        }
        .stat-block strong {
            display: block;
            font-size: 12px;
            margin-bottom: 3px;
        }
        .stat-block span {
            font-size: 16px;
            font-weight: bold;
            color: #000;
        }
        .stat-block .description {
            font-size: 9px;
            color: #666;
            margin-top: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            margin-bottom: 15px;
            font-size: 9px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 4px 5px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #e9e9e9;
            font-weight: bold;
        }
        tr:nth-child(even) td {
            background-color: #f8f8f8;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        /* Para simular colunas lado a lado sem flex real */
        .row::after {
            content: "";
            clear: both;
            display: table;
        }
        .column {
            float: left;
            width: 32%; /* Ajuste conforme necessário, considerando margens/padding */
            padding: 5px;
            box-sizing: border-box;
        }

    </style>
</head>
<body>
<div class="container">
    <h1>Dashboard de Produção</h1>
    <div class="header-info">Gerado em: {{ $date }}</div>

    <h2>Visão Geral da Produção</h2>
    <div class="stats-container row">
        @if(isset($productionStats) && count($productionStats) > 0)
            @foreach($productionStats as $stat)
                <div class="stat-block column">
                    <strong>{{ $stat->getLabel() }}</strong>
                    <span>{{ $stat->getValue() }}</span>
                    @if($stat->getDescription())
                        <p class="description">{{ $stat->getDescription() }}</p>
                    @endif
                </div>
            @endforeach
        @else
            <p>Nenhuma estatística de produção disponível.</p>
        @endif
    </div>
    <div style="clear:both;"></div> {{-- Limpa float --}}

    <h2>Resumo de Tempos de Pausa (Últimos 7 Dias)</h2>
    <div class="stats-container row">
        @if(isset($pauseStats) && count($pauseStats) > 0)
            @foreach($pauseStats as $stat)
                <div class="stat-block column">
                    <strong>{{ $stat->getLabel() }}</strong>
                    <span>{{ $stat->getValue() }}</span>
                    @if($stat->getDescription())
                        <p class="description">{{ $stat->getDescription() }}</p>
                    @endif
                </div>
            @endforeach
        @else
            <p>Nenhuma estatística de pausa disponível.</p>
        @endif
    </div>
    <div style="clear:both;"></div> {{-- Limpa float --}}


    <h2>Tempos Médios de Produção por Produto</h2>
    @if(isset($averageProductionTimesData) && $averageProductionTimesData->isNotEmpty())
        <table>
            <thead>
            <tr>
                <th>Produto</th>
                <th class="text-center">Tempo Efetivo Médio</th>
                <th class="text-center">Tempo Médio Pausa Não Produtiva</th>
                <th class="text-center">Ordens Concluídas (Base)</th>
            </tr>
            </thead>
            <tbody>
            @foreach($averageProductionTimesData as $item)
                <tr>
                    <td>{{ $item['product_name'] }}</td>
                    <td class="text-center">{{ $item['average_effective_duration'] }}</td>
                    <td class="text-center">{{ $item['average_non_productive_pause_duration'] }}</td>
                    <td class="text-center">{{ $item['completed_orders_count'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p>Nenhum dado de tempo médio de produção disponível.</p>
    @endif

</div>
</body>
</html>
