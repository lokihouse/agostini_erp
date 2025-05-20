<?php

namespace App\Filament\Exports;

use App\Models\SalesOrder;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class SalesOrderExporter extends Exporter
{
    protected static ?string $model = SalesOrder::class;

    public static function getColumns(): array
    {
        return [
ExportColumn::make('id')->label('ID'),
            ExportColumn::make('company_id')->label('Company Id'),
            ExportColumn::make('client_id')->label('Client Id'),
            ExportColumn::make('sales_visit_id')->label('Sales Visit Id'),
            ExportColumn::make('user_id')->label('User Id'),
            ExportColumn::make('order_number')->label('Order Number'),
            ExportColumn::make('order_date')->label('Order Date'),
            ExportColumn::make('delivery_deadline')->label('Delivery Deadline'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('total_amount')->label('Total Amount'),
            ExportColumn::make('notes')->label('Notes'),
            ExportColumn::make('cancellation_reason')->label('Cancellation Reason'),
            ExportColumn::make('cancellation_details')->label('Cancellation Details'),
            ExportColumn::make('cancelled_at')->label('Cancelled At')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your SalesOrder export has completed and ' . number_format($export->successful_rows) . ' ' . Str::plural('row', $export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . Str::plural('row', $failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getFileName(Export $export): string // Added for user convenience
    {
        return 'sales_orders_' . $export->getKey() . '.xlsx';
    }

    // Optional: You can define form components for exporter options
    // public static function getOptionsFormComponents(): array
    // {
    //     return [
    //         // Forms\Components\Checkbox::make('include_extra_data')->label('Include Extra Data'),
    //     ];
    // }
}