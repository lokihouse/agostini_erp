<?php

namespace App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource\Pages;

use App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource;
use Closure;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

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
