<?php

namespace App\Filament\Clusters\Sistema\Resources\UserResource\Pages;

use App\Filament\Clusters\Sistema\Resources\UserResource;
use App\Filament\Exports\UserExporter;
use App\Filament\Imports\UserImporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ImportAction::make()
                ->label('Importar usuários')
                ->iconButton()
                ->icon('heroicon-o-arrow-down-on-square')
                ->importer(UserImporter::class),
            Actions\ExportAction::make()
                ->label('Exportar usuários')
                ->iconButton()
                ->icon('heroicon-o-arrow-up-on-square')
                ->exporter(UserExporter::class),
            Actions\CreateAction::make(),
        ];
    }
}
