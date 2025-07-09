<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Visitas sem Pedido</title>
    <style>
        body { font-family: 'Inter', sans-serif; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 24px; margin: 0; }
        .header p { font-size: 12px; margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 11px; }
        th { background-color: #f2f2f2; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
<div class="header">
    <h1>Relatório de Visitas sem Pedido</h1>
    <p>Gerado em: {{ $date }}</p>
</div>

<table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
    <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
    <tr>
        <th scope="col" class="px-4 py-3">Cliente</th>
        <th scope="col" class="px-4 py-3">Vendedor</th>
        <th scope="col" class="px-4 py-3">Data Finalização</th>
        <th scope="col" class="px-4 py-3">Motivo (Sem Pedido)</th>
        <th scope="col" class="px-4 py-3">Ações Corretivas</th>
    </tr>
    </thead>
    <tbody>
    @foreach($visits as $visit)
        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
            <td class="px-4 py-2 font-medium text-gray-900 dark:text-white">
                {{ $visit->client?->name ?? 'N/A' }}
                @if($visit->client?->taxNumber)
                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $visit->client->taxNumber_formatted }}</span>
                @endif
            </td>
            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                {{ $visit->assignedTo?->name ?? 'N/A' }}
            </td>
            <td class="px-4 py-2 text-gray-700 dark:text-gray-300">
                {{ $visit->visit_end_time ? $visit->visit_end_time->format('d/m/Y H:i') : ($visit->visited_at ? $visit->visited_at->format('d/m/Y H:i') : 'N/A') }}
            </td>
            <td class="px-4 py-2 text-gray-700 dark:text-gray-300 text-xs">
                {{ $visit->report_reason_no_order ?: '-' }}
            </td>
            <td class="px-4 py-2 text-gray-700 dark:text-gray-300 text-xs">
                {{ $visit->report_corrective_actions ?: '-' }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
</body>
</html>

