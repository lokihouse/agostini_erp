<?php

namespace App\Filament\Imports;

use App\Models\TransportOrder;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\TextInput; // Example, adjust as needed
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash; // If you have password fields

class TransportOrderImporter extends Importer
{
    protected static ?string $model = TransportOrder::class;

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
            ImportColumn::make('transport_order_number')
                ->label('Transport Order Number')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('vehicle_id')
                ->label('Vehicle Id')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('driver_id')
                ->label('Driver Id')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('status')
                ->label('Status')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('planned_departure_datetime')
                ->label('Planned Departure Datetime')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('actual_departure_datetime')
                ->label('Actual Departure Datetime')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('planned_arrival_datetime')
                ->label('Planned Arrival Datetime')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('actual_arrival_datetime')
                ->label('Actual Arrival Datetime')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('cancellation_reason')
                ->label('Cancellation Reason')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('cancelled_at')
                ->label('Cancelled At')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('cancelled_by_user_id')
                ->label('Cancelled By User Id')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('notes')
                ->label('Notes')
                ->requiredMapping()
                ->rules(['nullable', 'max:255'])
        ];
    }

    public function resolveRecord(): ?TransportOrder
    {
        // Basic example: always creates a new record.
        // Customize this method to update existing records if needed.
        //
        // Example for updating or creating:
        // if ($this->data['id'] ?? null) {
        //      $record = TransportOrder::find($this->data['id']);
        //      if ($record) {
        //          return $record;
        //      }
        // }
        //
        // // Or using a unique business key:
        // // return TransportOrder::firstOrNew([
        // //     'email' => $this->data['email'],
        // // ]);

        return new TransportOrder();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your TransportOrder import has completed and ' . number_format($import->successful_rows) . ' ' . Str::plural('row', $import->successful_rows) . ' imported.';

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
    //        // '*.email' => ['required', 'email', 'unique:'.TransportOrder::class.',email'],
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