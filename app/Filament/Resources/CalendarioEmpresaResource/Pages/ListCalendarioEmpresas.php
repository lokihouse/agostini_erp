<?php

namespace App\Filament\Resources\CalendarioEmpresaResource\Pages;

use App\Filament\Resources\CalendarioEmpresaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCalendarioEmpresas extends ListRecords
{
    protected static string $resource = CalendarioEmpresaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
