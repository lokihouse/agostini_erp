<?php

namespace App\Filament\Clusters\Cadastros\Resources\EquipamentoResource\Pages;

use App\Filament\Clusters\Cadastros\Resources\EquipamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEquipamento extends EditRecord
{
    protected static string $resource = EquipamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
