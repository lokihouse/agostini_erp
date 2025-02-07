<?php

namespace App\Filament\Resources\JornadaDeTrabalhoResource\Pages;

use App\Filament\Resources\JornadaDeTrabalhoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJornadaDeTrabalho extends EditRecord
{
    protected static string $resource = JornadaDeTrabalhoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['agenda']);
        unset($data['carga_horaria_acumulada']);
        return parent::mutateFormDataBeforeSave($data);
    }
}
