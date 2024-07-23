<?php

namespace App\Filament\Imports;

use App\Models\Produto;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ProdutoImporter extends Importer
{
    protected static ?string $model = Produto::class;

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
            ImportColumn::make('valor_unitario')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('mapa_de_producao'),
            ImportColumn::make('tempo_producao')
                ->numeric()
                ->rules(['integer']),
        ];
    }

    public function resolveRecord(): ?Produto
    {
        // return Produto::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Produto();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your produto import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
