<?php

namespace App\Filament\Exports;

use App\Models\Holiday;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class HolidayExporter extends Exporter
{
    protected static ?string $model = Holiday::class;

    public static function getColumns(): array
    {
        return [
ExportColumn::make('id')->label('ID'),
            ExportColumn::make('company_id')->label('Company Id'),
            ExportColumn::make('name')->label('Name'),
            ExportColumn::make('date')->label('Date'),
            ExportColumn::make('type')->label('Type'),
            ExportColumn::make('is_recurrent')->label('Is Recurrent'),
            ExportColumn::make('notes')->label('Notes')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your Holiday export has completed and ' . number_format($export->successful_rows) . ' ' . Str::plural('row', $export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . Str::plural('row', $failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getFileName(Export $export): string // Added for user convenience
    {
        return 'holidays_' . $export->getKey() . '.xlsx';
    }

    // Optional: You can define form components for exporter options
    // public static function getOptionsFormComponents(): array
    // {
    //     return [
    //         // Forms\Components\Checkbox::make('include_extra_data')->label('Include Extra Data'),
    //     ];
    // }
}