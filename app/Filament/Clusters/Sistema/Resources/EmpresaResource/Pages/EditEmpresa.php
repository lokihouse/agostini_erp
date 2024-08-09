<?php

namespace App\Filament\Clusters\Sistema\Resources\EmpresaResource\Pages;

use App\Filament\Clusters\Sistema\Resources\EmpresaResource;
use App\Utils\MyTextFormater;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmpresa extends EditRecord
{
    protected static string $resource = EmpresaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);

        $data['horarios'] = json_decode($data['horarios'], true);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = parent::mutateFormDataBeforeSave($data);

        $data['cnpj'] = MyTextFormater::clear($data['cnpj']);
        $data['telefone'] = MyTextFormater::clear($data['telefone']);
        $data['cep'] = MyTextFormater::clear($data['cep']);

        $data['raio_cerca'] = (int) $data['raio_cerca'];
        $data['tolerancia_turno'] = (int) $data['tolerancia_turno'];
        $data['tolerancia_jornada'] = (int) $data['tolerancia_jornada'];
        $data['justificativa_dias'] = (int) $data['justificativa_dias'];

        return $data;
    }
}
