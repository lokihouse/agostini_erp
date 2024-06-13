<?php

namespace App\Filament\Clusters\Vendas\Resources\VisitaResource\Pages;

use App\Filament\Actions\VisitaAgendar;
use App\Filament\Clusters\Vendas\Resources\VisitaResource;
use App\Models\Visita;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Set;
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
