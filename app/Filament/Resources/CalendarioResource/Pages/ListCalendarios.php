<?php

namespace App\Filament\Resources\CalendarioResource\Pages;

use App\Filament\Resources\CalendarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCalendarios extends ListRecords
{
    protected static string $resource = CalendarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Adicionar Data'),
        ];
    }
}
