<?php

namespace App\Filament\Resources\PlanoDeContaResource\Pages;

use App\Filament\Resources\PlanoDeContaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlanoDeContas extends ListRecords
{
    protected static string $resource = PlanoDeContaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
