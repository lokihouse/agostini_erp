<?php

namespace App\Filament\Widgets;

use App\Models\ProductionOrder;
use Carbon\Carbon;

// Importar Carbon
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductionStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1; // Ordem no dashboard (opcional)

    protected function getStats(): array
    {
        // 1. Total de Ordens (excluindo canceladas, talvez?)
        $totalOrders = ProductionOrder::query()
            // ->where('status', '!=', 'Cancelada') // Descomente se quiser excluir canceladas
            ->count();

        // 2. Ordens Atrasadas
        $overdueOrders = ProductionOrder::query()
            ->where('due_date', '<', Carbon::today()) // Data limite no passado
            ->whereNotIn('status', ['Concluída', 'Cancelada']) // Que não estão concluídas ou canceladas
            ->count();

        // 3. Ordens em Execução
        $inProgressOrders = ProductionOrder::query()
            ->where('status', 'Em Andamento')
            ->count();

        return [
            Stat::make('Total de Ordens', $totalOrders)
                ->description('Número total de ordens registradas')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make('Ordens Atrasadas', $overdueOrders)
                ->description('Ordens com data limite vencida')
                ->descriptionIcon('heroicon-m-clock')
                // Muda a cor se houver atrasadas
                ->color($overdueOrders > 0 ? 'danger' : 'success'),

            Stat::make('Ordens em Execução', $inProgressOrders)
                ->description('Ordens com status "Em Andamento"')
                ->descriptionIcon('heroicon-m-cog')
                ->color('warning'),
        ];
    }
}
