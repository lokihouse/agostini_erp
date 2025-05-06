<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\ProductionOrder;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\CarbonInterval;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class AverageProductionTimes extends BaseWidget
{
    protected static ?int $sort = 2; // Ordem no dashboard
    protected int | string | array $columnSpan = 'full'; // Ocupar largura total

    protected static ?string $heading = 'Tempo Médio de Produção por Produto';

    public function table(Table $table): Table
    {
        return $table
            // Usamos uma query base em Product e calculamos a média
            ->query(Product::query())
            ->columns([
                TextColumn::make('name')
                    ->label('Produto')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('average_duration')
                    ->label('Tempo Médio')
                    ->getStateUsing(function (Product $record): ?string {
                        // Calcula a média de duração para este produto
                        $averageSeconds = ProductionOrder::query()
                            ->whereHas('items', function (Builder $query) use ($record) {
                                $query->where('product_uuid', $record->uuid);
                            })
                            ->where('status', 'Concluída') // Apenas ordens concluídas
                            ->whereNotNull('start_date')   // Que tenham data de início
                            ->whereNotNull('completion_date') // E data de conclusão
                            // Calcula a diferença em segundos e depois a média
                            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, start_date, completion_date)) as avg_duration')
                            ->value('avg_duration'); // Pega o valor calculado

                        if ($averageSeconds > 0) {
                            // Formata os segundos para um formato legível (ex: 1d 2h 30m)
                            return CarbonInterval::seconds($averageSeconds)->cascade()->forHumans(['short' => true]);
                        }

                        return '-'; // Retorna '-' se não houver dados para calcular
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        // Ordenação customizada (pode ser complexa ou removida se não essencial)
                        // Ordenar por média calculada é difícil diretamente aqui.
                        // Poderíamos ordenar pelo nome do produto como fallback.
                        return $query->orderBy('name', $direction);
                    }),

                TextColumn::make('completed_orders_count')
                    ->label('Ordens Concluídas (Base)')
                    ->getStateUsing(function (Product $record): int {
                        // Conta quantas ordens formam a base do cálculo
                        return ProductionOrder::query()
                            ->whereHas('items', function (Builder $query) use ($record) {
                                $query->where('product_uuid', $record->uuid);
                            })
                            ->where('status', 'Concluída')
                            ->whereNotNull('start_date')
                            ->whereNotNull('completion_date')
                            ->count();
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        // Ordenar por contagem também é complexo aqui.
                        return $query->orderBy('name', $direction); // Fallback
                    }),
            ])
            ->defaultSort('name', 'asc');
    }
}
