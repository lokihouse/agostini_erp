<?php

namespace App\Filament\Clusters\Cargas\Resources\OrdemDeExpedicaoResource\Pages;

use App\Filament\Clusters\Cargas\Resources\OrdemDeExpedicaoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrdemDeExpedicao extends EditRecord
{
    protected static string $resource = OrdemDeExpedicaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
