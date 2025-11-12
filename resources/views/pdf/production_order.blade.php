<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordem de Produção - {{ $order->order_number }}</title>
    <style>
        /* Configuração da página com margens mínimas */
        @page {
            margin: 15px 20px; /* Margem vertical de 15px, horizontal de 20px */
        }

        /* Estilos otimizados */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px; /* Fonte menor */
            line-height: 1.2; /* Espaçamento entre linhas reduzido */
            color: #333;
        }
        img{
            width: 150px;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 0;
        }
        h1, h2, h3 {
            margin-top: 5px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        h1 { font-size: 16px; text-align: center; margin-bottom: 10px; }
        /* Ajuste no H2 para etapas */
        h2.steps-title { font-size: 12px; border-bottom: 1px solid #ccc; padding-bottom: 2px; margin-top: 15px; }
        /* Subtítulo para o produto nas etapas */
        h3.product-steps-title { font-size: 10px; margin-top: 10px; margin-bottom: 3px; font-weight: bold; }
        h3 { font-size: 10px; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            margin-bottom: 10px;
            table-layout: fixed; /* Ajuda a controlar larguras de coluna */
        }
        /* Tabela de itens e cabeçalho podem ter layout auto */
        .header-table, .items-table {
            table-layout: auto;
        }
        /* Tabela de etapas mantém layout fixo */
        .steps-table {
            table-layout: fixed;
            margin-bottom: 15px; /* Espaço extra após cada tabela de etapas */
        }

        th, td {
            border: 1px solid #ddd;
            padding: 3px 4px;
            text-align: left;
            vertical-align: middle;
            word-wrap: break-word; /* Quebra palavras longas */
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        /* Estilos para o cabeçalho em formato de tabela */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border: 1px solid #eee; /* Borda externa opcional */
            background-color: #fafafa; /* Fundo opcional */
        }
        .header-table td {
            border: none; /* Remove bordas internas da tabela de cabeçalho */
            padding: 2px 4px; /* Padding interno das células do cabeçalho (ajustado) */
            vertical-align: top;
            width: 16.66%; /* Aproximadamente 1/6 da largura */
        }
        .header-table strong {
            font-weight: bold;
            display: block; /* Faz o label ficar acima do valor */
            margin-bottom: 1px;
            color: #555; /* Cor mais suave para o label */
        }

        .notes-section {
            margin-top: 10px;
            border: 1px solid #eee;
            padding: 5px;
        }
        .notes-section h3 { margin-bottom: 3px; }

        /* --- Estilos da Tabela de Etapas (Mantendo suas definições) --- */
        th.qr-code-header{
            width: 10%; /* Sua definição */
            padding: 2px; /* Padding menor para cabeçalho vazio */
        }

        td.qr-code-cell {
            padding: 6px 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            vertical-align: middle;
        }

        td.qr-code-cell div.debug_code {
            font-size: 5px;
            color: red;
            padding-top: 6px;
        }

        /* Coluna Ordem - Alinhamento Central */
        th.order-header, td.order-cell {
            text-align: center;
            width: 7%; /* Sua definição */
        }

        /* Coluna Etapa - Largura Máxima */
        th.step-header, td.step-cell {
            width: 30%; /* Sua definição */
        }

        /* Coluna Descrição - Ocupa o restante (width não necessário com table-layout: fixed) */
        th.description-header, td.description-cell {
            /* Width calculado automaticamente */
        }
        /* --- Fim dos Estilos da Tabela de Etapas --- */

        /* Para evitar quebras de página */
        tr { page-break-inside: avoid; }
        h2, h3, .notes-section, .header-table, .steps-table { page-break-inside: avoid; }
        h2, .notes-section { page-break-before: auto; page-break-after: avoid; }

    </style>
</head>
<body>
     <header>
        <div>
            <img src="images/logo-agostini-full_color-1-horizontal.png" alt="Agostini Tecnologia de Gestão">
        </div>
    </header>
<div class="container">
    <h1>Ordem de Produção: {{ $order->order_number }}</h1>

    {{-- Cabeçalho em formato de tabela com 6 colunas --}}
    <table class="header-table">
        <tr>
            <td>
                <strong>Empresa:</strong>
                {{ $order->company->name ?? 'N/A' }}
            </td>
            <td>
                <strong>Status:</strong>
                {{ $order->status }}
            </td>
            <td>
                <strong>Data Limite:</strong>
                {{ $order->due_date ? $order->due_date->format('d/m/Y') : 'N/A' }}
            </td>
            <td>
                <strong>Data Emissão:</strong>
                {{ $order->created_at->format('d/m/Y H:i') }}
            </td>
            <td>
                <strong>Responsável:</strong>
                {{ $order->user->name ?? 'N/A' }}
            </td>
            <td>
                <strong>Data Início:</strong>
                {{ $order->start_date ? $order->start_date->format('d/m/Y H:i') : 'N/A' }}
            </td>
        </tr>
    </table>
    {{-- Fim do Cabeçalho --}}

    <h2>Itens a Produzir</h2>
    {{-- Tabela de Itens --}}
    <table class="items-table">
        <thead>
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 35%;">Produto</th>
            <th style="width: 15%;">SKU</th>
            <th style="width: 10%;">Qtd. Plan.</th>
            <th style="width: 5%;">Un.</th>
            <th style="width: 30%;">Obs. Item</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($order->items as $index => $item)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $item->product->name ?? 'Produto não encontrado' }}</td>
                <td>{{ $item->product->sku ?? '-' }}</td>
                <td style="text-align: right;">{{ rtrim(rtrim(number_format($item->quantity_planned, 4, ',', '.'), '0'), ',') }}</td>
                <td style="text-align: center;">{{ $item->product->unit_of_measure ?? 'un' }}</td>
                <td>{{ $item->notes }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align: center;">Nenhum item nesta ordem.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{-- Seção para as Etapas de Produção POR ITEM --}}
    <h2 class="steps-title">Etapas de Produção por Item</h2>

    {{-- Loop através de cada item da ordem --}}
    @forelse ($order->items as $item)
        @php
            // A relação agora é 'productionSteps' (plural) e é uma coleção
        @endphp

        {{-- Mostra o nome do produto como um subtítulo --}}
        <h3 class="product-steps-title">Produto: {{ $item->product->name ?? 'Produto não encontrado' }} (Item #{{ $loop->iteration }})</h3>

        {{-- Verifica se o item tem etapas de produção associadas --}}
        @if($item->productionSteps->isNotEmpty())
            {{-- Tabela para exibir as VÁRIAS etapas deste item --}}
            <table class="steps-table">
                <thead>
                <tr>
                    <th class="qr-code-header">QR Code</th>
                    <th class="order-header">Seq.</th>
                    <th class="step-header">Etapa de Produção</th>
                    <th class="description-header">Descrição da Etapa</th>
                </tr>
                </thead>
                <tbody>
                {{-- Loop através de cada etapa do item --}}
                @foreach($item->productionSteps()->orderBy('name')->get() as $step)
                    <tr>
                        <td class="qr-code-cell">
                            @php
                                $qrData = $item->uuid . ':' . $step->uuid;
                            @endphp
                            {!! DNS2D::getBarcodeHTML($qrData, 'QRCODE', 1.8, 1.8) !!}

                            {{--@if (env('APP_DEBUG', false))
                                <div class="debug_code">
                                    {{ \Illuminate\Support\Facades\Crypt::encryptString($qrData) }}
                                </div>
                            @endif--}}
                        </td>
                        <td class="order-cell">{{ $loop->parent->iteration }}.{{$loop->iteration}}</td>
                        <td class="step-cell">{{ $step->name ?? 'N/A' }}</td>
                        <td class="description-cell">{{ $step->description ?? '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <div style="text-align: center; font-style: italic; padding: 5px; border: 1px dashed #ccc; margin-bottom: 10px;">Nenhuma etapa de produção definida para este item.</div>
        @endif

    @empty
        <p style="font-size: 9px; text-align: center;">Nenhum item na ordem para listar etapas.</p>
    @endforelse
    {{-- Fim da Seção de Etapas por Item --}}


    @if($order->notes)
        <div class="notes-section">
            <h3>Observações Gerais da Ordem:</h3>
            <p>{!! nl2br(e($order->notes)) !!}</p>
        </div>
    @endif

</div>
</body>
</html>
