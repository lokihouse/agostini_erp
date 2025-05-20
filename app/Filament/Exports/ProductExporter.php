<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
ExportColumn::make('id')->label('ID'),
            ExportColumn::make('company_id')->label('Company Id'),
            ExportColumn::make('name')->label('Name'),
            ExportColumn::make('sku')->label('Sku'),
            ExportColumn::make('description')->label('Description'),
            ExportColumn::make('unit_of_measure')->label('Unit Of Measure'),
            ExportColumn::make('standard_cost')->label('Standard Cost'),
            ExportColumn::make('sale_price')->label('Sale Price'),
            ExportColumn::make('minimum_sale_price')->label('Minimum Sale Price')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your Product export has completed and ' . number_format($export->successful_rows) . ' ' . Str::plural('row', $export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . Str::plural('row', $failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getFileName(Export $export): string // Added for user convenience
    {
        return 'products_' . $export->getKey() . '.xlsx';
    }

    // Optional: You can define form components for exporter options
    // public static function getOptionsFormComponents(): array
    // {
    //     return [
    //         // Forms\Components\Checkbox::make('include_extra_data')->label('Include Extra Data'),
    //     ];
    // }
}