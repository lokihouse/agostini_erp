<?php

namespace App\Filament\Exports;

use App\Models\Empresa;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class EmpresaExporter extends Exporter
{
    protected static ?string $model = Empresa::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('cnpj'),
            ExportColumn::make('razao_social'),
            ExportColumn::make('nome_fantasia'),
            ExportColumn::make('logradouro'),
            ExportColumn::make('numero'),
            ExportColumn::make('complemento'),
            ExportColumn::make('bairro'),
            ExportColumn::make('municipio'),
            ExportColumn::make('uf'),
            ExportColumn::make('cep'),
            ExportColumn::make('email'),
            ExportColumn::make('telefone'),
            ExportColumn::make('latitude'),
            ExportColumn::make('longitude'),
            ExportColumn::make('raio_cerca'),
            ExportColumn::make('horarios'),
            ExportColumn::make('tolerancia_turno'),
            ExportColumn::make('tolerancia_jornada'),
            ExportColumn::make('justificativa_dias'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your empresa export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
