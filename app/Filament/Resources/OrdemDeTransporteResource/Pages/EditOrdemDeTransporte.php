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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['rota'] = json_decode($data['rota'], true);
        $data['entregas'] = json_decode($data['entregas'], true);
        return parent::mutateFormDataBeforeFill($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['rota'] = json_encode($data['rota'] ?? []);
        $data['entregas'] = json_encode($data['entregas'] ?? []);
        return parent::mutateFormDataBeforeSave($data);
    }
}
