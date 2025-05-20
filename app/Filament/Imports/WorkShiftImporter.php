<?php

namespace App\Filament\Imports;

use App\Models\WorkShift;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\TextInput; // Example, adjust as needed
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash; // If you have password fields

class WorkShiftImporter extends Importer
{
    protected static ?string $model = WorkShift::class;

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
            ImportColumn::make('name')
                ->label('Name')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('type')
                ->label('Type')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('notes')
                ->label('Notes')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('cycle_work_duration_hours')
                ->label('Cycle Work Duration Hours')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('cycle_off_duration_hours')
                ->label('Cycle Off Duration Hours')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('cycle_shift_starts_at')
                ->label('Cycle Shift Starts At')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('cycle_shift_ends_at')
                ->label('Cycle Shift Ends At')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('cycle_interval_starts_at')
                ->label('Cycle Interval Starts At')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('cycle_interval_ends_at')
                ->label('Cycle Interval Ends At')
                ->requiredMapping()
                ->rules(['nullable', 'max:255'])
        ];
    }

    public function resolveRecord(): ?WorkShift
    {
        // Basic example: always creates a new record.
        // Customize this method to update existing records if needed.
        //
        // Example for updating or creating:
        // if ($this->data['id'] ?? null) {
        //      $record = WorkShift::find($this->data['id']);
        //      if ($record) {
        //          return $record;
        //      }
        // }
        //
        // // Or using a unique business key:
        // // return WorkShift::firstOrNew([
        // //     'email' => $this->data['email'],
        // // ]);

        return new WorkShift();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your WorkShift import has completed and ' . number_format($import->successful_rows) . ' ' . Str::plural('row', $import->successful_rows) . ' imported.';

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
    //        // '*.email' => ['required', 'email', 'unique:'.WorkShift::class.',email'],
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