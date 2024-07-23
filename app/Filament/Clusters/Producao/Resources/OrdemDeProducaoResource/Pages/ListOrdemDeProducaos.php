<?php

namespace App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource\Pages;

use App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrdemDeProducaos extends ListRecords
{
    protected static string $resource = OrdemDeProducaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
