<?php

namespace App\Filament\Resources\OrdemDeTransporteResource\Pages;

use App\Filament\Resources\OrdemDeTransporteResource;
use App\Forms\Components\CargasMapaRotaFormField;
use Filament\Actions;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;

class ListOrdemDeTransportes extends ListRecords
{
    protected static string $resource = OrdemDeTransporteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
