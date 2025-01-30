<?php

namespace App\Filament\Resources\JornadaDeTrabalhoResource\Pages;

use App\Filament\Resources\JornadaDeTrabalhoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJornadaDeTrabalhos extends ListRecords
{
    protected static string $resource = JornadaDeTrabalhoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
