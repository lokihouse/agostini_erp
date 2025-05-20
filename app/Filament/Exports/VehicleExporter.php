<?php

namespace App\Filament\Exports;

use App\Models\Vehicle;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class VehicleExporter extends Exporter
{
    protected static ?string $model = Vehicle::class;

    public static function getColumns(): array
    {
        return [
ExportColumn::make('id')->label('ID'),
            ExportColumn::make('company_id')->label('Company Id'),
            ExportColumn::make('license_plate')->label('License Plate'),
            ExportColumn::make('description')->label('Description'),
            ExportColumn::make('brand')->label('Brand'),
            ExportColumn::make('model_name')->label('Model Name'),
            ExportColumn::make('year_manufacture')->label('Year Manufacture'),
            ExportColumn::make('year_model')->label('Year Model'),
            ExportColumn::make('color')->label('Color'),
            ExportColumn::make('cargo_volume_m3')->label('Cargo Volume M3'),
            ExportColumn::make('max_load_kg')->label('Max Load Kg'),
            ExportColumn::make('renavam')->label('Renavam'),
            ExportColumn::make('is_active')->label('Is Active'),
            ExportColumn::make('notes')->label('Notes')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your Vehicle export has completed and ' . number_format($export->successful_rows) . ' ' . Str::plural('row', $export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . Str::plural('row', $failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getFileName(Export $export): string // Added for user convenience
    {
        return 'vehicles_' . $export->getKey() . '.xlsx';
    }

    // Optional: You can define form components for exporter options
    // public static function getOptionsFormComponents(): array
    // {
    //     return [
    //         // Forms\Components\Checkbox::make('include_extra_data')->label('Include Extra Data'),
    //     ];
    // }
}