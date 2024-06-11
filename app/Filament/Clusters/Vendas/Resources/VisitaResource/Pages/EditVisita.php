<?php

namespace App\Filament\Clusters\Vendas\Resources\VisitaResource\Pages;

use App\Filament\Clusters\Vendas\Resources\VisitaResource;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\EditRecord;

class EditVisita extends EditRecord
{
    protected static string $resource = VisitaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
