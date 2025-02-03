<?php

namespace App\Filament\Resources\OrdemDeTransporteResource\Pages;

use App\Filament\Resources\OrdemDeTransporteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrdemDeTransporte extends EditRecord
{
    protected static string $resource = OrdemDeTransporteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
