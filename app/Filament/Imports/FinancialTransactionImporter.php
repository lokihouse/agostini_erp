<?php

namespace App\Filament\Imports;

use App\Models\FinancialTransaction;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\TextInput; // Example, adjust as needed
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash; // If you have password fields

class FinancialTransactionImporter extends Importer
{
    protected static ?string $model = FinancialTransaction::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id')
                ->label('ID (for updates)')
                ->rules(['nullable', 'integer']),
            ImportColumn::make('company_id')
                ->label('Company Id')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('chart_of_account_uuid')
                ->label('Chart Of Account Uuid')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('description')
                ->label('Description')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('amount')
                ->label('Amount')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('type')
                ->label('Type')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('transaction_date')
                ->label('Transaction Date')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('user_id')
                ->label('User Id')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('notes')
                ->label('Notes')
                ->requiredMapping()
                ->rules(['nullable', 'max:255'])
        ];
    }

    public function resolveRecord(): ?FinancialTransaction
    {
        // Basic example: always creates a new record.
        // Customize this method to update existing records if needed.
        //
        // Example for updating or creating:
        // if ($this->data['id'] ?? null) {
        //      $record = FinancialTransaction::find($this->data['id']);
        //      if ($record) {
        //          return $record;
        //      }
        // }
        //
        // // Or using a unique business key:
        // // return FinancialTransaction::firstOrNew([
        // //     'email' => $this->data['email'],
        // // ]);

        return new FinancialTransaction();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your FinancialTransaction import has completed and ' . number_format($import->successful_rows) . ' ' . Str::plural('row', $import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . Str::plural('row', $failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    // Optional: You can define form components for importer options
    // public static function getOptionsFormComponents(): array
    // {
    //     return [
    //         // Forms\Components\Checkbox::make('update_existing')->label('Update existing records'),
    //     ];
    // }

    // Optional: Define a global data validation schema if needed
    // protected function getValidationRules(): array
    // {
    //    return [
    //        // '*.email' => ['required', 'email', 'unique:'.FinancialTransaction::class.',email'],
    //    ];
    // }

    // Optional: Process data before saving, e.g. hashing passwords
    // protected function beforeSave(array $data): array
    // {
    //     if (isset($data['password'])) {
    //         $data['password'] = Hash::make($data['password']);
    //     }
    //     return $data;
    // }
}