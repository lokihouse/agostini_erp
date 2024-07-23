<?php

namespace App\Filament\Clusters\Sistema\Resources\EmpresaResource\Pages;

use App\Filament\Clusters\Sistema\Resources\EmpresaResource;
use App\Filament\Exports\EmpresaExporter;
use App\Filament\Imports\EmpresaImporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmpresas extends ListRecords
{
    protected static string $resource = EmpresaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ImportAction::make()
                ->label('Importar empresas')
                ->iconButton()
                ->icon('heroicon-o-arrow-down-on-square')
                ->importer(EmpresaImporter::class),
            Actions\ExportAction::make()
                ->label('Exportar empresas')
                ->iconButton()
                ->icon('heroicon-o-arrow-up-on-square')
                ->exporter(EmpresaExporter::class),
            Actions\CreateAction::make(),
        ];
    }
}
