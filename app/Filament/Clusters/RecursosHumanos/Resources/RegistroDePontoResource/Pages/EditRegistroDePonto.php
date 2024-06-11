<?php

namespace App\Filament\Clusters\RecursosHumanos\Resources\RegistroDePontoResource\Pages;

use App\Filament\Clusters\RecursosHumanos\Resources\RegistroDePontoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRegistroDePonto extends EditRecord
{
    protected static string $resource = RegistroDePontoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
