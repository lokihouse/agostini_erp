<?php

namespace App\Filament\Imports;

use App\Models\TimeClockEntry;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\TextInput; // Example, adjust as needed
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash; // If you have password fields

class TimeClockEntryImporter extends Importer
{
    protected static ?string $model = TimeClockEntry::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('id')
                ->label('ID (for updates)')
                ->rules(['nullable', 'integer']),
            ImportColumn::make('user_id')
                ->label('User Id')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('company_id')
                ->label('Company Id')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('recorded_at')
                ->label('Recorded At')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('type')
                ->label('Type')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('status')
                ->label('Status')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('latitude')
                ->label('Latitude')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('longitude')
                ->label('Longitude')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('ip_address')
                ->label('Ip Address')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('user_agent')
                ->label('User Agent')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('notes')
                ->label('Notes')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('approved_by')
                ->label('Approved By')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('approved_at')
                ->label('Approved At')
                ->requiredMapping()
                ->rules(['nullable', 'max:255'])
        ];
    }

    public function resolveRecord(): ?TimeClockEntry
    {
        // Basic example: always creates a new record.
        // Customize this method to update existing records if needed.
        //
        // Example for updating or creating:
        // if ($this->data['id'] ?? null) {
        //      $record = TimeClockEntry::find($this->data['id']);
        //      if ($record) {
        //          return $record;
        //      }
        // }
        //
        // // Or using a unique business key:
        // // return TimeClockEntry::firstOrNew([
        // //     'email' => $this->data['email'],
        // // ]);

        return new TimeClockEntry();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your TimeClockEntry import has completed and ' . number_format($import->successful_rows) . ' ' . Str::plural('row', $import->successful_rows) . ' imported.';

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
    //        // '*.email' => ['required', 'email', 'unique:'.TimeClockEntry::class.',email'],
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