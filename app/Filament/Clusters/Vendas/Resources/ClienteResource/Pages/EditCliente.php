<?php

namespace App\Filament\Clusters\Vendas\Resources\ClienteResource\Pages;

use App\Filament\Clusters\Vendas\Resources\ClienteResource;
use App\Models\VendedoresPorCliente;
use App\Utils\TextFormater;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCliente extends EditRecord
{
    protected static string $resource = ClienteResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = parent::mutateFormDataBeforeSave($data);
        $data['cnpj'] = TextFormater::clear($data['cnpj']);
        $data['telefone'] = TextFormater::clear($data['telefone']);
        $data['latitude'] = $data['localizacao']['lat'];
        $data['longitude'] = $data['localizacao']['lng'];
        unset($data['localizacao']);

        if(isset($data['cep'])) $data['cep'] = TextFormater::clear($data['cep']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
