<?php

namespace App\Filament\Clusters\Cadastros\Resources\ProdutoResource\Pages;

use App\Filament\Clusters\Cadastros\Resources\ProdutoResource;
use App\Filament\Exports\ProdutoExporter;
use App\Filament\Imports\ProdutoImporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProdutos extends ListRecords
{
    protected static string $resource = ProdutoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ImportAction::make()
                ->label('Importar Produtos')
                ->iconButton()
                ->icon('heroicon-o-arrow-down-on-square')
                ->importer(ProdutoImporter::class),
            Actions\ExportAction::make()
                ->label('Exportar Produtos')
                ->iconButton()
                ->icon('heroicon-o-arrow-up-on-square')
                ->exporter(ProdutoExporter::class),
            Actions\CreateAction::make(),
        ];
    }
}
