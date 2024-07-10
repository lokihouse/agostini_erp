<?php

namespace App\Filament\Clusters\Sistema\Resources\EmpresaResource\Pages;

use App\Filament\Clusters\Sistema\Resources\EmpresaResource;
use App\Utils\TextFormater;
use Filament\Resources\Pages\CreateRecord;

class CreateEmpresa extends CreateRecord
{
    protected static string $resource = EmpresaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = parent::mutateFormDataBeforeCreate($data);

        $data['cnpj'] = TextFormater::clear($data['cnpj']);
        $data['telefone'] = TextFormater::clear($data['telefone']);
        $data['cep'] = TextFormater::clear($data['cep']);

        $data['latitude'] = (float) $data['localizacao']['lat'];
        $data['longitude'] = (float) $data['localizacao']['lng'];
        $data['horarios'] = json_encode($data['horarios']);

        $data['raio_cerca'] = (int) $data['raio_cerca'];
        $data['tolerancia_turno'] = (int) $data['tolerancia_turno'];
        $data['tolerancia_jornada'] = (int) $data['tolerancia_jornada'];
        $data['justificativa_dias'] = (int) $data['justificativa_dias'];

        return $data;
    }
}
