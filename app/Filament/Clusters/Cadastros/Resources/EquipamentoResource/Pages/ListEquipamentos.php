<?php

namespace App\Filament\Clusters\Cadastros\Resources\EquipamentoResource\Pages;

use App\Filament\Clusters\Cadastros\Resources\EquipamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEquipamentos extends ListRecords
{
    protected static string $resource = EquipamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
