<?php

namespace App\Filament\Clusters\Cadastros\Resources\DepartamentoResource\Pages;

use App\Filament\Clusters\Cadastros\Resources\DepartamentoResource;
use App\Filament\Exports\DepartamentoExporter;
use App\Filament\Imports\DepartamentoImporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDepartamentos extends ListRecords
{
    protected static string $resource = DepartamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ImportAction::make()
                ->label('Importar departamentos')
                ->iconButton()
                ->icon('heroicon-o-arrow-down-on-square')
                ->importer(DepartamentoImporter::class),
            Actions\ExportAction::make()
                ->label('Exportar departamentos')
                ->iconButton()
                ->icon('heroicon-o-arrow-up-on-square')
                ->exporter(DepartamentoExporter::class),
            Actions\CreateAction::make(),
        ];
    }
}
