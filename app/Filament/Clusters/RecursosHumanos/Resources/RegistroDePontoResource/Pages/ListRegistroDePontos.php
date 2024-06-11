<?php

namespace App\Filament\Clusters\RecursosHumanos\Resources\RegistroDePontoResource\Pages;

use App\Filament\Clusters\RecursosHumanos\Resources\RegistroDePontoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRegistroDePontos extends ListRecords
{
    protected static string $resource = RegistroDePontoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
