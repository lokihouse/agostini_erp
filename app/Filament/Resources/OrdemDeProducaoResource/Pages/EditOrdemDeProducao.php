<?php

namespace App\Filament\Resources\OrdemDeProducaoResource\Pages;

use App\Filament\Resources\OrdemDeProducaoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrdemDeProducao extends EditRecord
{
    protected static string $resource = OrdemDeProducaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
