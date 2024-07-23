<?php

namespace App\Filament\Clusters\Financeiro\Resources\PlanoDeContaResource\Pages;

use App\Filament\Clusters\Financeiro\Resources\PlanoDeContaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlanoDeConta extends EditRecord
{
    protected static string $resource = PlanoDeContaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
