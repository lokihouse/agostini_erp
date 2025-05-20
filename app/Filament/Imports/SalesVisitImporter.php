<?php

namespace App\Filament\Imports;

use App\Models\SalesVisit;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\TextInput; // Example, adjust as needed
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash; // If you have password fields

class SalesVisitImporter extends Importer
{
    protected static ?string $model = SalesVisit::class;

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
            ImportColumn::make('client_id')
                ->label('Client Id')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('scheduled_by_user_id')
                ->label('Scheduled By User Id')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('assigned_to_user_id')
                ->label('Assigned To User Id')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('scheduled_at')
                ->label('Scheduled At')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('visited_at')
                ->label('Visited At')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('status')
                ->label('Status')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('notes')
                ->label('Notes')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('cancellation_reason')
                ->label('Cancellation Reason')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('cancellation_details')
                ->label('Cancellation Details')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('sales_order_id')
                ->label('Sales Order Id')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('visit_start_time')
                ->label('Visit Start Time')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('visit_end_time')
                ->label('Visit End Time')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('report_reason_no_order')
                ->label('Report Reason No Order')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('report_corrective_actions')
                ->label('Report Corrective Actions')
                ->requiredMapping()
                ->rules(['nullable', 'max:255'])
        ];
    }

    public function resolveRecord(): ?SalesVisit
    {
        // Basic example: always creates a new record.
        // Customize this method to update existing records if needed.
        //
        // Example for updating or creating:
        // if ($this->data['id'] ?? null) {
        //      $record = SalesVisit::find($this->data['id']);
        //      if ($record) {
        //          return $record;
        //      }
        // }
        //
        // // Or using a unique business key:
        // // return SalesVisit::firstOrNew([
        // //     'email' => $this->data['email'],
        // // ]);

        return new SalesVisit();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your SalesVisit import has completed and ' . number_format($import->successful_rows) . ' ' . Str::plural('row', $import->successful_rows) . ' imported.';

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
    //        // '*.email' => ['required', 'email', 'unique:'.SalesVisit::class.',email'],
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