<?php

namespace App\Filament\Clusters\Sistema\Resources\EmpresaResource\Pages;

use App\Filament\Actions\Form\EmpresaAtivar;
use App\Filament\Actions\Form\EmpresaDesativar;
use App\Filament\Clusters\Sistema\Resources\EmpresaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmpresa extends EditRecord
{
    protected static string $resource = EmpresaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EmpresaAtivar::make('ativar'),
            EmpresaDesativar::make('desativar'),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = parent::mutateFormDataBeforeSave($data);

        $data['latitude'] = $data['cerca_geografica_mapa']['latitude'];
        $data['longitude'] = $data['cerca_geografica_mapa']['longitude'];

        return $data;
    }
}
