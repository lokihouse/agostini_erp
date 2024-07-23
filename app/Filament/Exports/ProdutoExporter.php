<?php

namespace App\Filament\Exports;

use App\Models\Produto;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ProdutoExporter extends Exporter
{
    protected static ?string $model = Produto::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('empresa_id'),
            ExportColumn::make('nome'),
            ExportColumn::make('descricao'),
            ExportColumn::make('valor_unitario'),
            ExportColumn::make('mapa_de_producao'),
            ExportColumn::make('tempo_producao'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your produto export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
