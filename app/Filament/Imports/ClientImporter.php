<?php

namespace App\Filament\Imports;

use App\Models\Client;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\TextInput; // Example, adjust as needed
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash; // If you have password fields

class ClientImporter extends Importer
{
    protected static ?string $model = Client::class;

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
            ImportColumn::make('social_name')
                ->label('Social Name')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('taxNumber')
                ->label('Tax Number')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('state_registration')
                ->label('State Registration')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('municipal_registration')
                ->label('Municipal Registration')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('email')
                ->label('Email')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('phone_number')
                ->label('Phone Number')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('website')
                ->label('Website')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('address_street')
                ->label('Address Street')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('address_number')
                ->label('Address Number')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('address_complement')
                ->label('Address Complement')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('address_district')
                ->label('Address District')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('address_city')
                ->label('Address City')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('address_state')
                ->label('Address State')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('address_zip_code')
                ->label('Address Zip Code')
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
            ImportColumn::make('status')
                ->label('Status')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('notes')
                ->label('Notes')
                ->requiredMapping()
                ->rules(['nullable', 'max:255'])
        ];
    }

    public function resolveRecord(): ?Client
    {
        // Basic example: always creates a new record.
        // Customize this method to update existing records if needed.
        //
        // Example for updating or creating:
        // if ($this->data['id'] ?? null) {
        //      $record = Client::find($this->data['id']);
        //      if ($record) {
        //          return $record;
        //      }
        // }
        //
        // // Or using a unique business key:
        // // return Client::firstOrNew([
        // //     'email' => $this->data['email'],
        // // ]);

        return new Client();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your Client import has completed and ' . number_format($import->successful_rows) . ' ' . Str::plural('row', $import->successful_rows) . ' imported.';

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
    //        // '*.email' => ['required', 'email', 'unique:'.Client::class.',email'],
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
