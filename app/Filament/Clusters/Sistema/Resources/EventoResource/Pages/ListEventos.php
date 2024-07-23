<?php

namespace App\Filament\Clusters\Sistema\Resources\EventoResource\Pages;

use App\Filament\Clusters\Sistema\Resources\EventoResource;
use App\Filament\Exports\EventoExporter;
use App\Filament\Imports\EventoImporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEventos extends ListRecords
{
    protected static string $resource = EventoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ImportAction::make()
                ->label('Importar empresas')
                ->iconButton()
                ->icon('heroicon-o-arrow-down-on-square')
                ->importer(EventoImporter::class),
            Actions\ExportAction::make()
                ->label('Exportar empresas')
                ->iconButton()
                ->icon('heroicon-o-arrow-up-on-square')
                ->exporter(EventoExporter::class),
            Actions\CreateAction::make(),
        ];
    }
}
