<?php

namespace App\Filament\Clusters\Vendas\Resources\ClienteResource\Pages;

use App\Filament\Clusters\Vendas\Resources\ClienteResource;
use App\Models\Visita;
use App\Utils\TextFormater;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCliente extends CreateRecord
{
    protected static string $resource = ClienteResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = parent::mutateFormDataBeforeCreate($data);

        $data['empresa_id'] = auth()->user()->empresa_id;

        $data['cnpj'] = TextFormater::clear($data['cnpj']);
        $data['telefone'] = TextFormater::clear($data['telefone']);
        $data['latitude'] = $data['localizacao']['lat'];
        $data['longitude'] = $data['localizacao']['lng'];
        unset($data['localizacao']);

        if(isset($data['cep'])) $data['cep'] = TextFormater::clear($data['cep']);

        return $data;
    }
}
