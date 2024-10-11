<?php

namespace App\Filament\Clusters\Cadastros\Resources\FuncionarioResource\Pages;

use App\Filament\Actions\ExportAction;
use App\Filament\Actions\ImportAction;
use App\Filament\Clusters\Cadastros\Resources\FuncionarioResource;
use App\Filament\Exports\FuncionarioExporter;
use App\Filament\Imports\FuncionarioImporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFuncionarios extends ListRecords
{
    protected static string $resource = FuncionarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make()->exporter(FuncionarioExporter::class),
            Actions\CreateAction::make(),
        ];
    }
}
