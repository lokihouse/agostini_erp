<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Precificação</title>
    <style>
         body { font-family: 'Inter', sans-serif; color: #333; }
         img{width: 150px;}
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 24px; margin: 0; }
        .header p { font-size: 12px; margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 11px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <header>
        <div>
            <img src="images/logo-agostini-full_color-1-horizontal.png" alt="Agostini Tecnologia de Gestão">
        </div>
    </header>
    <div class="header">
         <h1>Tabela de Precificação</h1>
         <p>Gerado em: {{ \Carbon\Carbon::now()->translatedFormat('d/m/y H:i') }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Custo Matéria Prima</th>
                <th>Despesas</th>
                <th>Imposto</th>
                <th>Comissão</th>
                <th>Frete</th>
                <th>Prazo</th>
                <th>VPC</th>
                <th>Assistência</th>
                <th>Inadimplência</th>
            {{--<th>Lucro</th>--}}  
            {{--<th>Custo Total</th>--}}
            {{--<th>Comercialização</th>--}}
                <th>Lucro Total</th>
                <th>Preço Final</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pricingTables as $pt)
                <tr>
                    <td>{{ $pt->product->name }}</td>
                    <td>R$ {{ number_format($pt->custo_materia_prima, 2, ',', '.')}}</td>
                    <td>R$ {{ number_format($pt->valorDespesas, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($pt->valorImposto, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($pt->valorComissao, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($pt->valorFrete, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($pt->valorPrazo, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($pt->valorVPC, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($pt->valorAssistencia, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($pt->valorInadimplencia, 2, ',', '.') }}</td>
                {{--<td>R$ {{ number_format($pt->lucro, 2, ',', '.') }}</td>--}}
                {{--<td>R$ {{ number_format($pt->custo_produto, 2, ',', '.') }}</td>--}}
                {{--<td>R$ {{ number_format($pt->comercializacao, 2, ',', '.') }}</td>--}}
                    <td>R$ {{ number_format($pt->lucro_total, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($pt->preco_final, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
