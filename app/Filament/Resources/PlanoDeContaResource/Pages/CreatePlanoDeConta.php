<?php

namespace App\Filament\Resources\PlanoDeContaResource\Pages;

use App\Filament\Resources\PlanoDeContaResource;
use App\Models\PlanoDeConta;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePlanoDeConta extends CreateRecord
{
    protected static string $resource = PlanoDeContaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['empresa_id'] = auth()->user()->empresa_id;

        if($data['plano_de_conta_id'] == null) {
            $codigoPlanoDeConta = PlanoDeConta::query()
                ->where('empresa_id', $data['empresa_id'])
                ->whereNull('plano_de_conta_id')
                ->get();
            $radical = '';
        }else {
            $codigoPlanoDeConta = PlanoDeConta::query()
                ->where('empresa_id', $data['empresa_id'])
                ->where('plano_de_conta_id', $data['plano_de_conta_id'])
                ->get();

            $radical = PlanoDeConta::query()
                ->where('id', $data['plano_de_conta_id'])
                ->first()->codigo . '.';
        }

        $data['codigo'] = $radical . $codigoPlanoDeConta->count() + 1;

        // dd($codigoPlanoDeConta, $radical, $data);

        return parent::mutateFormDataBeforeCreate($data);
    }
}
