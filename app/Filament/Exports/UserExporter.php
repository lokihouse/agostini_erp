<?php

namespace App\Filament\Exports;

use App\Models\User;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class UserExporter extends Exporter
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
ExportColumn::make('id')->label('ID'),
            ExportColumn::make('company_id')->label('Company Id'),
            ExportColumn::make('work_shift_id')->label('Work Shift Id'),
            ExportColumn::make('name')->label('Name'),
            ExportColumn::make('username')->label('Username'),
            ExportColumn::make('password')->label('Password'),
            ExportColumn::make('is_active')->label('Is Active')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your User export has completed and ' . number_format($export->successful_rows) . ' ' . Str::plural('row', $export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . Str::plural('row', $failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getFileName(Export $export): string // Added for user convenience
    {
        return 'users_' . $export->getKey() . '.xlsx';
    }

    // Optional: You can define form components for exporter options
    // public static function getOptionsFormComponents(): array
    // {
    //     return [
    //         // Forms\Components\Checkbox::make('include_extra_data')->label('Include Extra Data'),
    //     ];
    // }
}