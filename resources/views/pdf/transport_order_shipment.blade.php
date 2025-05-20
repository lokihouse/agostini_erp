<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documento de Carga - {{ $transportOrder->transport_order_number }}</title>
    <style>
        @page {
            margin: 8mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            line-height: 1.3;
            color: #333;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h1 {
            margin: 0 0 5px 0;
            font-size: 18px;
        }

        .header p {
            margin: 2px 0;
            font-size: 10px;
        }

        h2 {
            font-size: 14px;
            margin-top: 15px;
            margin-bottom: 8px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 3px;
        }

        .client-cluster {
            border: 1px solid #b0b0b0;
            padding: 8px;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .client-cluster-header {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 8px;
            background-color: #f0f0f0;
            padding: 5px;
            border-bottom: 1px solid #b0b0b0;
        }

        .item-details {
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px dotted #ddd;
            page-break-inside: avoid;
        }

        .item-details:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .qr-code {
            float: left;
            margin-right: 10px;
            margin-left: 0;
        }

        .qr-code img {
            width: 60px;
            height: 60px;
        }

        .item-info {
            overflow: hidden;
        }

        .item-info p {
            margin: 2px 0;
        }

        .product-name-highlight {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .no-print {
            display: none;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Documento de Acompanhamento de Carga</h1>
        <p><strong>Ordem de Transporte:</strong> {{ $transportOrder->transport_order_number }}</p>
        @if($transportOrder->vehicle)
            <p><strong>Veículo:</strong> {{ $transportOrder->vehicle->license_plate }}
                - {{ $transportOrder->vehicle->description }}</p>
        @endif
        @if($transportOrder->driver)
            <p><strong>Motorista:</strong> {{ $transportOrder->driver->name }}</p>
        @endif
        <p><strong>Data de Emissão:</strong> {{ now()->format('d/m/Y H:i') }}</p>
        @if($transportOrder->planned_departure_datetime)
            <p><strong>Saída Prevista:</strong> {{ $transportOrder->planned_departure_datetime->format('d/m/Y H:i') }}
            </p>
        @endif
    </div>

    <h2>Itens da Entrega</h2>

    @if($transportOrder->items->isEmpty())
        <p>Nenhum item de entrega nesta ordem.</p>
    @else
        @php
            $groupedItems = $transportOrder->items->sortBy('delivery_sequence')->groupBy('client_id');
        @endphp

        @foreach($groupedItems as $clientId => $clientItems)
            @php
                $client = $clientItems->first()->client;
            @endphp
            <div class="client-cluster">
                <div class="client-cluster-header">
                    Cliente: {{ $client->name ?? 'N/A' }} (CNPJ/CPF: {{ $client->tax_number ?? 'N/A' }}) <br>
                    Endereço Principal: {{ $client->getFullAddress() }}
                </div>

                @foreach($clientItems as $itemIndex => $item)
                    <div class="item-details clearfix">
                        <div class="qr-code">
                            <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($item->uuid, 'QRCODE', 2,2) }}"
                                 alt="QR Code">
                        </div>
                        <div class="item-info">
                            <p class="product-name-highlight">
                                {{ $item->product->name ?? 'N/A' }}
                            </p>
                            <p>
                                <strong>Quantidade:</strong> {{ number_format($item->quantity, 2, ',', '.') }} {{ $item->product->unit_of_measure ?? '' }}
                            </p>
                            @if($item->notes)
                                <p><strong>Obs. Item:</strong> {{ $item->notes }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    @endif

    @if($transportOrder->notes)
        <h2>Observações Gerais da Ordem de Transporte:</h2>
        <p>{{ $transportOrder->notes }}</p>
    @endif

</div>
</body>
</html>
