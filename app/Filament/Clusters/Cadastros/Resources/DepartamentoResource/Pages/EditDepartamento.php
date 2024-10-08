<?php

namespace App\Filament\Clusters\Cadastros\Resources\DepartamentoResource\Pages;

use App\Filament\Clusters\Cadastros\Resources\DepartamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDepartamento extends EditRecord
{
    protected static string $resource = DepartamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
