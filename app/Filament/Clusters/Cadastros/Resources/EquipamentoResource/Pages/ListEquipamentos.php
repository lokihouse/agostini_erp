<?php

namespace App\Filament\Clusters\Cadastros\Resources\EquipamentoResource\Pages;

use App\Filament\Actions\ExportAction;
use App\Filament\Clusters\Cadastros\Resources\EquipamentoResource;
use App\Filament\Exports\EquipamentoExporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEquipamentos extends ListRecords
{
    protected static string $resource = EquipamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()->exporter(EquipamentoExporter::class),
            Actions\CreateAction::make(),
        ];
    }
}
