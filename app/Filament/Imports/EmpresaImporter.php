<?php

namespace App\Filament\Imports;

use App\Models\Empresa;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class EmpresaImporter extends Importer
{
    protected static ?string $model = Empresa::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('cnpj')
                ->requiredMapping()
                ->rules(['required', 'max:14']),
            ImportColumn::make('razao_social')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('nome_fantasia')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('logradouro')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('numero')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('complemento')
                ->rules(['max:255']),
            ImportColumn::make('bairro')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('municipio')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('uf')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('cep')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('email')
                ->rules(['email', 'max:255']),
            ImportColumn::make('telefone')
                ->rules(['max:255']),
            ImportColumn::make('latitude')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('longitude')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('raio_cerca')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('horarios'),
            ImportColumn::make('tolerancia_turno')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('tolerancia_jornada')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('justificativa_dias')
                ->numeric()
                ->rules(['integer']),
        ];
    }

    public function resolveRecord(): ?Empresa
    {
        // return Empresa::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Empresa();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your empresa import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
