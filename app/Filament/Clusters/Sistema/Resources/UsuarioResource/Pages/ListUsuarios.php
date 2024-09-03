<?php

namespace App\Filament\Clusters\Sistema\Resources\UsuarioResource\Pages;

use App\Filament\Clusters\Sistema\Resources\UsuarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsuarios extends ListRecords
{
    protected static string $resource = UsuarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
