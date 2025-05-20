<?php

namespace App\Filament\Exports;

use App\Models\FinancialTransaction;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class FinancialTransactionExporter extends Exporter
{
    protected static ?string $model = FinancialTransaction::class;

    public static function getColumns(): array
    {
        return [
ExportColumn::make('id')->label('ID'),
            ExportColumn::make('company_id')->label('Company Id'),
            ExportColumn::make('chart_of_account_uuid')->label('Chart Of Account Uuid'),
            ExportColumn::make('description')->label('Description'),
            ExportColumn::make('amount')->label('Amount'),
            ExportColumn::make('type')->label('Type'),
            ExportColumn::make('transaction_date')->label('Transaction Date'),
            ExportColumn::make('user_id')->label('User Id'),
            ExportColumn::make('notes')->label('Notes')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your FinancialTransaction export has completed and ' . number_format($export->successful_rows) . ' ' . Str::plural('row', $export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . Str::plural('row', $failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getFileName(Export $export): string // Added for user convenience
    {
        return 'financial_transactions_' . $export->getKey() . '.xlsx';
    }

    // Optional: You can define form components for exporter options
    // public static function getOptionsFormComponents(): array
    // {
    //     return [
    //         // Forms\Components\Checkbox::make('include_extra_data')->label('Include Extra Data'),
    //     ];
    // }
}