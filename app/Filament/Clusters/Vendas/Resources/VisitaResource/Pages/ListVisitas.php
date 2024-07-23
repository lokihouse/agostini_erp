<?php

namespace App\Filament\Clusters\Vendas\Resources\VisitaResource\Pages;

use App\Filament\Actions\VisitaAgendar;
use App\Filament\Clusters\Vendas\Resources\VisitaResource;
use Filament\Resources\Pages\ListRecords;

class ListVisitas extends ListRecords
{
    protected static string $resource = VisitaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            VisitaAgendar::make('agendar_visita'),
        ];
    }
}
