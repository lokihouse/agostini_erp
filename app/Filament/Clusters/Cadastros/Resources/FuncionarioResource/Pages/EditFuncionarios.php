<?php

namespace App\Filament\Clusters\Cadastros\Resources\FuncionarioResource\Pages;

use App\Filament\Clusters\Cadastros\Resources\FuncionarioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFuncionarios extends EditRecord
{
    protected static string $resource = FuncionarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
