<?php

namespace App\Filament\Exports;

use App\Models\TimeClockEntry;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class TimeClockEntryExporter extends Exporter
{
    protected static ?string $model = TimeClockEntry::class;

    public static function getColumns(): array
    {
        return [
ExportColumn::make('id')->label('ID'),
            ExportColumn::make('user_id')->label('User Id'),
            ExportColumn::make('company_id')->label('Company Id'),
            ExportColumn::make('recorded_at')->label('Recorded At'),
            ExportColumn::make('type')->label('Type'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('latitude')->label('Latitude'),
            ExportColumn::make('longitude')->label('Longitude'),
            ExportColumn::make('ip_address')->label('Ip Address'),
            ExportColumn::make('user_agent')->label('User Agent'),
            ExportColumn::make('notes')->label('Notes'),
            ExportColumn::make('approved_by')->label('Approved By'),
            ExportColumn::make('approved_at')->label('Approved At')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your TimeClockEntry export has completed and ' . number_format($export->successful_rows) . ' ' . Str::plural('row', $export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . Str::plural('row', $failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getFileName(Export $export): string // Added for user convenience
    {
        return 'time_clock_entries_' . $export->getKey() . '.xlsx';
    }

    // Optional: You can define form components for exporter options
    // public static function getOptionsFormComponents(): array
    // {
    //     return [
    //         // Forms\Components\Checkbox::make('include_extra_data')->label('Include Extra Data'),
    //     ];
    // }
}