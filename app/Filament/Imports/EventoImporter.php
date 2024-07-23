<?php

namespace App\Filament\Imports;

use App\Models\Evento;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class EventoImporter extends Importer
{
    protected static ?string $model = Evento::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('empresa')
                ->relationship(),
            ImportColumn::make('nome')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('descricao'),
            ImportColumn::make('tipo'),
            ImportColumn::make('credito_debito')
                ->requiredMapping()
                ->rules(['required']),
        ];
    }

    public function resolveRecord(): ?Evento
    {
        // return Evento::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Evento();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your evento import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
