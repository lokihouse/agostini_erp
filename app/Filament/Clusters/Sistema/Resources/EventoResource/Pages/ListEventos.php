<?php

namespace App\Filament\Clusters\Sistema\Resources\EventoResource\Pages;

use App\Filament\Clusters\Sistema\Resources\EventoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEventos extends ListRecords
{
    protected static string $resource = EventoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
