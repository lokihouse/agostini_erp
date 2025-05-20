<?php

namespace App\Filament\Exports;

use App\Models\ProductionOrder;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class ProductionOrderExporter extends Exporter
{
    protected static ?string $model = ProductionOrder::class;

    public static function getColumns(): array
    {
        return [
ExportColumn::make('id')->label('ID'),
            ExportColumn::make('company_id')->label('Company Id'),
            ExportColumn::make('order_number')->label('Order Number'),
            ExportColumn::make('due_date')->label('Due Date'),
            ExportColumn::make('start_date')->label('Start Date'),
            ExportColumn::make('completion_date')->label('Completion Date'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('notes')->label('Notes'),
            ExportColumn::make('user_uuid')->label('User Uuid')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your ProductionOrder export has completed and ' . number_format($export->successful_rows) . ' ' . Str::plural('row', $export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . Str::plural('row', $failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getFileName(Export $export): string // Added for user convenience
    {
        return 'production_orders_' . $export->getKey() . '.xlsx';
    }

    // Optional: You can define form components for exporter options
    // public static function getOptionsFormComponents(): array
    // {
    //     return [
    //         // Forms\Components\Checkbox::make('include_extra_data')->label('Include Extra Data'),
    //     ];
    // }
}