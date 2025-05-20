<?php

namespace App\Filament\Exports;

use App\Models\TransportOrder;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class TransportOrderExporter extends Exporter
{
    protected static ?string $model = TransportOrder::class;

    public static function getColumns(): array
    {
        return [
ExportColumn::make('id')->label('ID'),
            ExportColumn::make('company_id')->label('Company Id'),
            ExportColumn::make('transport_order_number')->label('Transport Order Number'),
            ExportColumn::make('vehicle_id')->label('Vehicle Id'),
            ExportColumn::make('driver_id')->label('Driver Id'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('planned_departure_datetime')->label('Planned Departure Datetime'),
            ExportColumn::make('actual_departure_datetime')->label('Actual Departure Datetime'),
            ExportColumn::make('planned_arrival_datetime')->label('Planned Arrival Datetime'),
            ExportColumn::make('actual_arrival_datetime')->label('Actual Arrival Datetime'),
            ExportColumn::make('cancellation_reason')->label('Cancellation Reason'),
            ExportColumn::make('cancelled_at')->label('Cancelled At'),
            ExportColumn::make('cancelled_by_user_id')->label('Cancelled By User Id'),
            ExportColumn::make('notes')->label('Notes')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your TransportOrder export has completed and ' . number_format($export->successful_rows) . ' ' . Str::plural('row', $export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . Str::plural('row', $failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getFileName(Export $export): string // Added for user convenience
    {
        return 'transport_orders_' . $export->getKey() . '.xlsx';
    }

    // Optional: You can define form components for exporter options
    // public static function getOptionsFormComponents(): array
    // {
    //     return [
    //         // Forms\Components\Checkbox::make('include_extra_data')->label('Include Extra Data'),
    //     ];
    // }
}