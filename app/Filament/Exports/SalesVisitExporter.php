<?php

namespace App\Filament\Exports;

use App\Models\SalesVisit;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class SalesVisitExporter extends Exporter
{
    protected static ?string $model = SalesVisit::class;

    public static function getColumns(): array
    {
        return [
ExportColumn::make('id')->label('ID'),
            ExportColumn::make('company_id')->label('Company Id'),
            ExportColumn::make('client_id')->label('Client Id'),
            ExportColumn::make('scheduled_by_user_id')->label('Scheduled By User Id'),
            ExportColumn::make('assigned_to_user_id')->label('Assigned To User Id'),
            ExportColumn::make('scheduled_at')->label('Scheduled At'),
            ExportColumn::make('visited_at')->label('Visited At'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('notes')->label('Notes'),
            ExportColumn::make('cancellation_reason')->label('Cancellation Reason'),
            ExportColumn::make('cancellation_details')->label('Cancellation Details'),
            ExportColumn::make('sales_order_id')->label('Sales Order Id'),
            ExportColumn::make('visit_start_time')->label('Visit Start Time'),
            ExportColumn::make('visit_end_time')->label('Visit End Time'),
            ExportColumn::make('report_reason_no_order')->label('Report Reason No Order'),
            ExportColumn::make('report_corrective_actions')->label('Report Corrective Actions')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your SalesVisit export has completed and ' . number_format($export->successful_rows) . ' ' . Str::plural('row', $export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . Str::plural('row', $failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getFileName(Export $export): string // Added for user convenience
    {
        return 'sales_visits_' . $export->getKey() . '.xlsx';
    }

    // Optional: You can define form components for exporter options
    // public static function getOptionsFormComponents(): array
    // {
    //     return [
    //         // Forms\Components\Checkbox::make('include_extra_data')->label('Include Extra Data'),
    //     ];
    // }
}