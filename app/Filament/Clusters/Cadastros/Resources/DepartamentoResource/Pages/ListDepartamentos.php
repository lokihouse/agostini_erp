<?php

namespace App\Filament\Clusters\Cadastros\Resources\DepartamentoResource\Pages;

use App\Filament\Actions\ExportAction;
use App\Filament\Clusters\Cadastros\Resources\DepartamentoResource;
use App\Filament\Exports\DepartamentoExporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDepartamentos extends ListRecords
{
    protected static string $resource = DepartamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()->exporter(DepartamentoExporter::class),
            Actions\CreateAction::make(),
        ];
    }
}
