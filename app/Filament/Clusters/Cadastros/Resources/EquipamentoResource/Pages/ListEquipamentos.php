<?php

namespace App\Filament\Clusters\Cadastros\Resources\EquipamentoResource\Pages;

use App\Filament\Clusters\Cadastros\Resources\EquipamentoResource;
use App\Filament\Exports\EquipamentoExporter;
use App\Filament\Imports\EquipamentoImporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEquipamentos extends ListRecords
{
    protected static string $resource = EquipamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ImportAction::make()
                ->label('Importar departamentos')
                ->iconButton()
                ->icon('heroicon-o-arrow-down-on-square')
                ->importer(EquipamentoImporter::class),
            Actions\ExportAction::make()
                ->label('Exportar departamentos')
                ->iconButton()
                ->icon('heroicon-o-arrow-up-on-square')
                ->exporter(EquipamentoExporter::class),
            Actions\CreateAction::make(),
        ];
    }
}
