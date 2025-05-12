<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido de Venda - {{ $order->order_number }}</title>
    <style>
        @page {
            margin: 15px 20px; /* AJUSTE: Margens da página um pouco menores */
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9.5px; /* AJUSTE: Fonte base ligeiramente menor */
            line-height: 1.25; /* AJUSTE: Altura da linha um pouco menor */
            color: #333;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header, .footer {
            width: 100%;
            text-align: center;
            position: fixed;
        }
        .header { top: -5px; }
        .footer { bottom: 0px; font-size: 8px; }
        .pagenum:before {
            content: "Página " counter(page);
        }
        h1, h2 {
            margin-top: 3px; /* AJUSTE: Margem superior menor */
            margin-bottom: 5px; /* AJUSTE: Margem inferior menor */
            font-weight: bold;
        }
        h1 { font-size: 16px; text-align: center; margin-bottom: 10px; } /* AJUSTE: Margem inferior de h1 */
        h2 { font-size: 13px; border-bottom: 1px solid #ccc; padding-bottom: 2px; margin-top: 10px; } /* AJUSTE: Margem superior e padding de h2 */

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3px; /* AJUSTE: Margem superior da tabela menor */
            margin-bottom: 7px; /* AJUSTE: Margem inferior da tabela menor */
        }
        th, td {
            border: 1px solid #ddd;
            padding: 3px 4px; /* AJUSTE: Padding das células menor */
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        .info-section-table {
            width: 100%;
            margin-bottom: 10px; /* AJUSTE: Margem inferior menor */
            border-collapse: collapse;
        }
        .info-section-table > tbody > tr > td {
            width: 50%;
            vertical-align: top;
            padding: 0;
            border: none;
        }
        .info-box {
            padding: 5px; /* AJUSTE: Padding interno da caixa menor */
            border: 1px solid #eee;
            background-color: #fdfdfd;
            margin: 0 3px 3px 0; /* AJUSTE: Margens da caixa menores */
            /* height: 100%; */ /* REMOVIDO: Deixar altura ser definida pelo conteúdo */
        }
        .info-box:last-child {
            margin-right: 0;
        }

        .info-box strong {
            display: inline-block;
            min-width: 80px; /* AJUSTE: Largura mínima do label menor */
            font-weight: bold; /* Garantir que o strong seja negrito */
        }
        .info-box div { margin-bottom: 2px; } /* AJUSTE: Margem inferior dos itens da caixa menor */


        .items-table th, .items-table td {
            font-size: 8.5px; /* AJUSTE: Fonte da tabela de itens ainda menor */
            padding: 2px 3px; /* AJUSTE: Padding da tabela de itens menor */
        }
        .items-table th.quantity, .items-table td.quantity,
        .items-table th.price, .items-table td.price,
        .items-table th.total, .items-table td.total {
            text-align: right;
        }
        .totals-section {
            margin-top: 10px; /* AJUSTE: Margem superior menor */
            float: right;
            width: 40%;
        }
        .totals-section table {
            width: 100%;
        }
        .totals-section td {
            border: none;
            padding: 2px 4px; /* AJUSTE: Padding menor */
        }
        .totals-section .grand-total td {
            font-weight: bold;
            border-top: 1px solid #333;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .notes-section {
            margin-top: 15px; /* AJUSTE: Margem superior menor */
            padding-top: 7px; /* AJUSTE: Padding superior menor */
            border-top: 1px dashed #ccc;
        }
        .notes-section p {
            margin-bottom: 3px; /* AJUSTE: Margem inferior dos parágrafos nas notas */
        }
    </style>
</head>
<body>
<div class="footer">
    <span class="pagenum"></span>
</div>

<div class="container">
    <h1>Pedido de Venda: {{ $order->order_number }}</h1>

    <table class="info-section-table">
        <tr>
            <td>
                <div class="info-box">
                    <h2>Informações do Pedido</h2>
                    <div><strong>Status:</strong> {{ \App\Models\SalesOrder::getStatusOptions()[$order->status] ?? $order->status }}</div>
                    <div><strong>Data do Pedido:</strong> {{ $order->order_date->format('d/m/Y') }}</div>
                    <div><strong>Prazo de Entrega:</strong> {{ $order->delivery_deadline ? $order->delivery_deadline->format('d/m/Y') : 'N/A' }}</div>
                    <div><strong>Criado por:</strong> {{ $order->user->name ?? 'N/A' }}</div>
                    @if($order->salesVisit)
                        <div><strong>Visita Associada:</strong> {{ $order->salesVisit->scheduled_at->format('d/m/Y H:i') }} ({{ $order->salesVisit->assignedTo->name ?? 'N/A' }})</div>
                    @endif
                </div>
            </td>
            <td>
                <div class="info-box">
                    <h2>Cliente</h2>
                    <div><strong>Nome Fantasia:</strong> {{ $order->client->name ?? 'N/A' }}</div>
                    <div><strong>Razão Social:</strong> {{ $order->client->social_name ?? 'N/A' }}</div>
                    <div><strong>CNPJ:</strong> {{ $order->client->tax_number ? \App\Utils\Cnpj::format($order->client->tax_number) : 'N/A' }}</div>
                    <div><strong>Email:</strong> {{ $order->client->email ?? 'N/A' }}</div>
                    <div><strong>Telefone:</strong> {{ $order->client->phone_number ?? 'N/A' }}</div>
                    <div>
                        <strong>Endereço:</strong>
                        {{ $order->client->address_street ?? '' }}
                        {{ $order->client->address_number ? ', ' . $order->client->address_number : '' }}
                        {{ $order->client->address_complement ? ' - ' . $order->client->address_complement : '' }}<br>
                        {{ $order->client->address_district ?? '' }} - {{ $order->client->address_city ?? '' }}/{{ $order->client->address_state ?? '' }}
                        {{ $order->client->address_zip_code ? ' - CEP: ' . $order->client->address_zip_code : '' }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <h2>Itens do Pedido</h2>
    <table class="items-table">
        <thead>
        <tr>
            <th>#</th>
            <th>Produto</th>
            <th>SKU</th>
            <th class="quantity">Qtd.</th>
            <th class="price">Preço Unit.</th>
            <th class="price">Desconto</th>
            <th class="price">Preço Final</th>
            <th class="total">Total Item</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($order->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->product->name ?? 'Produto não encontrado' }}</td>
                <td>{{ $item->product->sku ?? '-' }}</td>
                <td class="quantity">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                <td class="price">R$ {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                <td class="price">R$ {{ number_format($item->discount_amount, 2, ',', '.') }}</td>
                <td class="price">R$ {{ number_format($item->final_price, 2, ',', '.') }}</td>
                <td class="total">R$ {{ number_format($item->total_price, 2, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center">Nenhum item neste pedido.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="clearfix">
        <div class="totals-section">
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">R$ {{ number_format($order->items->sum('total_price'), 2, ',', '.') }}</td>
                </tr>
                {{-- Adicionar outros totais como frete, impostos, se houver --}}
                <tr class="grand-total">
                    <td>TOTAL DO PEDIDO:</td>
                    <td class="text-right">R$ {{ number_format($order->total_amount, 2, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    @if($order->notes)
        <div class="notes-section">
            <h2>Observações do Pedido:</h2>
            <p>{!! nl2br(e($order->notes)) !!}</p>
        </div>
    @endif

    @if($order->status === \App\Models\SalesOrder::STATUS_CANCELLED)
        <div class="notes-section" style="margin-top: 15px; border-top: 1px solid #ff0000; color: #ff0000;">
            <h2 style="color: #ff0000;">Pedido Cancelado</h2>
            <p><strong>Motivo:</strong> {{ $order->cancellation_reason ?? 'Não informado' }}</p>
            @if($order->cancellation_details)
                <p><strong>Detalhes:</strong> {!! nl2br(e($order->cancellation_details)) !!}</p>
            @endif
            <p><strong>Data do Cancelamento:</strong> {{ $order->cancelled_at ? $order->cancelled_at->format('d/m/Y H:i') : 'N/A' }}</p>
        </div>
    @endif

</div>
</body>
</html>
