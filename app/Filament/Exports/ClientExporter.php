<?php

namespace App\Filament\Exports;

use App\Models\Client;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class ClientExporter extends Exporter
{
    protected static ?string $model = Client::class;

    public static function getColumns(): array
    {
        return [
ExportColumn::make('id')->label('ID'),
            ExportColumn::make('company_id')->label('Company Id'),
            ExportColumn::make('name')->label('Name'),
            ExportColumn::make('social_name')->label('Social Name'),
            ExportColumn::make('tax_number')->label('Tax Number'),
            ExportColumn::make('state_registration')->label('State Registration'),
            ExportColumn::make('municipal_registration')->label('Municipal Registration'),
            ExportColumn::make('email')->label('Email'),
            ExportColumn::make('phone_number')->label('Phone Number'),
            ExportColumn::make('website')->label('Website'),
            ExportColumn::make('address_street')->label('Address Street'),
            ExportColumn::make('address_number')->label('Address Number'),
            ExportColumn::make('address_complement')->label('Address Complement'),
            ExportColumn::make('address_district')->label('Address District'),
            ExportColumn::make('address_city')->label('Address City'),
            ExportColumn::make('address_state')->label('Address State'),
            ExportColumn::make('address_zip_code')->label('Address Zip Code'),
            ExportColumn::make('latitude')->label('Latitude'),
            ExportColumn::make('longitude')->label('Longitude'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('notes')->label('Notes')
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your Client export has completed and ' . number_format($export->successful_rows) . ' ' . Str::plural('row', $export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . Str::plural('row', $failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getFileName(Export $export): string // Added for user convenience
    {
        return 'clients_' . $export->getKey() . '.xlsx';
    }

    // Optional: You can define form components for exporter options
    // public static function getOptionsFormComponents(): array
    // {
    //     return [
    //         // Forms\Components\Checkbox::make('include_extra_data')->label('Include Extra Data'),
    //     ];
    // }
}