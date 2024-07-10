<?php

namespace App\Filament\Exports;

use App\Models\Departamento;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class DepartamentoExporter extends Exporter
{
    protected static ?string $model = Departamento::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('empresa_id'),
            ExportColumn::make('nome'),
            ExportColumn::make('descricao'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your departamento export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
