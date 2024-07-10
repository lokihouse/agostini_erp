<?php

namespace App\Filament\Imports;

use App\Models\Departamento;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class DepartamentoImporter extends Importer
{
    protected static ?string $model = Departamento::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('empresa_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('nome')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('descricao'),
        ];
    }

    public function resolveRecord(): ?Departamento
    {
        // return Departamento::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Departamento();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your departamento import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
