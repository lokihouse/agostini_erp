<?php

namespace App\Filament\Exports;

use App\Models\WorkShift;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class WorkShiftExporter extends Exporter
{
    protected static ?string $model = WorkShift::class;

    public static function getColumns(): array
    {
        return [
ExportColumn::make('id')->label('ID'),
            ExportColumn::make('company_id')->label('Company Id'),
            ExportColumn::make('name')->label('Name'),
            ExportColumn::make('type')->label('Type'),
            ExportColumn::make('notes')->label('Notes'),
            ExportColumn::make('cycle_work_duration_hours')->label('Cycle Work Duration Hours'),
            ExportColumn::make('cycle_off_duration_hours')->label('Cycle Off Duration Hours'),
            ExportColumn::make('cycle_shift_starts_at')->label('Cycle Shift Starts At'),
            ExportColumn::make('cycle_shift_ends_at')->label('Cycle Shift Ends At'),
            ExportColumn::make('cycle_interval_starts_at')->label('Cycle Interval Starts At'),
            ExportColumn::make('cycle_interval_ends_at')->label('Cycle Interval Ends At')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your WorkShift export has completed and ' . number_format($export->successful_rows) . ' ' . Str::plural('row', $export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . Str::plural('row', $failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getFileName(Export $export): string // Added for user convenience
    {
        return 'work_shifts_' . $export->getKey() . '.xlsx';
    }

    // Optional: You can define form components for exporter options
    // public static function getOptionsFormComponents(): array
    // {
    //     return [
    //         // Forms\Components\Checkbox::make('include_extra_data')->label('Include Extra Data'),
    //     ];
    // }
}