<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\TaskPauseLog;
use App\Models\PauseReason;
use Carbon\CarbonInterval;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log; // Para debug, se necessário
use Illuminate\Support\Collection; // Adicionar Collection

class AverageProductionTimes extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Tempos Médios de Produção por Produto';

    // ... (método table() permanece o mesmo) ...
    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query())
            ->columns([
                TextColumn::make('name')
                    ->label('Produto')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('average_effective_duration')
                    ->label('Tempo Efetivo Médio')
                    ->getStateUsing(function (Product $productRecord): ?string {
                        $stats = $this->calculateOrderStatsForProduct($productRecord);
                        if ($stats['validOrderCount'] === 0) {
                            return '-';
                        }
                        $averageEffectiveSeconds = $stats['totalEffectiveSecondsForAllOrders'] / $stats['validOrderCount'];
                        return $averageEffectiveSeconds > 0 ? CarbonInterval::seconds($averageEffectiveSeconds)->cascade()->forHumans(['short' => true, 'parts' => 3]) : '0s';
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('name', $direction);
                    }),

                TextColumn::make('average_non_productive_pause_duration')
                    ->label('Tempo Médio Pausa Não Produtiva')
                    ->getStateUsing(function (Product $productRecord): ?string {
                        $stats = $this->calculateOrderStatsForProduct($productRecord);
                        if ($stats['validOrderCount'] === 0) {
                            return '-';
                        }
                        $averageNonProductivePauseSeconds = $stats['totalNonProductivePauseSecondsForAllOrders'] / $stats['validOrderCount'];
                        return $averageNonProductivePauseSeconds > 0 ? CarbonInterval::seconds($averageNonProductivePauseSeconds)->cascade()->forHumans(['short' => true, 'parts' => 3]) : '0s';
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('name', $direction);
                    }),

                TextColumn::make('completed_orders_count')
                    ->label('Ordens Concluídas (Base)')
                    ->getStateUsing(function (Product $productRecord): int {
                        $stats = $this->calculateOrderStatsForProduct($productRecord);
                        return $stats['validOrderCount']; // Reutiliza o dado calculado
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('name', $direction);
                    }),
            ])
            ->defaultSort('name', 'asc');
    }


    protected function calculateOrderStatsForProduct(Product $productRecord): array
    {
        $completedOrders = ProductionOrder::query()
            ->whereHas('items', function (Builder $query) use ($productRecord) {
                $query->where('product_uuid', $productRecord->uuid);
            })
            ->where('status', ProductionOrder::STATUS_COMPLETED) //Correção aplicada
            ->whereNotNull('start_date')
            ->whereNotNull('completion_date')
            ->with('items:uuid,production_order_uuid')
            ->get();

        if ($completedOrders->isEmpty()) {
            return [
                'totalEffectiveSecondsForAllOrders' => 0,
                'totalNonProductivePauseSecondsForAllOrders' => 0,
                'validOrderCount' => 0,
            ];
        }

        $totalEffectiveSecondsForAllOrders = 0;
        $totalNonProductivePauseSecondsForAllOrders = 0;
        $validOrderCount = 0;

        foreach ($completedOrders as $order) {
            $leadTimeSeconds = $order->completion_date->diffInSeconds($order->start_date);
            $itemUuids = $order->items->pluck('uuid')->all();
            $currentOrderNonProductivePauseSeconds = 0;

            if (!empty($itemUuids)) {
                $currentOrderNonProductivePauseSeconds = TaskPauseLog::query()
                    ->join('pause_reasons', 'task_pause_logs.pause_reason_uuid', '=', 'pause_reasons.uuid')
                    ->whereIn('task_pause_logs.production_order_item_uuid', $itemUuids)
                    ->whereIn('pause_reasons.type', [
                        PauseReason::TYPE_DEAD_TIME,
                        PauseReason::TYPE_MANDATORY_BREAK
                    ])
                    ->whereNotNull('task_pause_logs.duration_seconds')
                    ->sum('task_pause_logs.duration_seconds');
            }

            $effectiveSecondsForOrder = max(0, $leadTimeSeconds - $currentOrderNonProductivePauseSeconds);

            $totalEffectiveSecondsForAllOrders += $effectiveSecondsForOrder;
            $totalNonProductivePauseSecondsForAllOrders += $currentOrderNonProductivePauseSeconds;
            $validOrderCount++;
        }

        return [
            'totalEffectiveSecondsForAllOrders' => $totalEffectiveSecondsForAllOrders,
            'totalNonProductivePauseSecondsForAllOrders' => $totalNonProductivePauseSecondsForAllOrders,
            'validOrderCount' => $validOrderCount,
        ];
    }

    /**
     * Método público para obter os dados formatados para o relatório PDF.
     */
    public function getPdfReportData(): Collection
    {
        $products = Product::query()->orderBy('name')->get();
        $reportData = collect();

        foreach ($products as $product) {
            $stats = $this->calculateOrderStatsForProduct($product);

            $averageEffectiveSeconds = ($stats['validOrderCount'] > 0)
                ? ($stats['totalEffectiveSecondsForAllOrders'] / $stats['validOrderCount'])
                : 0;

            $averageNonProductivePauseSeconds = ($stats['validOrderCount'] > 0)
                ? ($stats['totalNonProductivePauseSecondsForAllOrders'] / $stats['validOrderCount'])
                : 0;

            $reportData->push([
                'product_name' => $product->name,
                'average_effective_duration' => $averageEffectiveSeconds > 0 ? CarbonInterval::seconds($averageEffectiveSeconds)->cascade()->forHumans(['short' => true, 'parts' => 3]) : '0s',
                'average_non_productive_pause_duration' => $averageNonProductivePauseSeconds > 0 ? CarbonInterval::seconds($averageNonProductivePauseSeconds)->cascade()->forHumans(['short' => true, 'parts' => 3]) : '0s',
                'completed_orders_count' => $stats['validOrderCount'],
            ]);
        }
        return $reportData;
    }
}
