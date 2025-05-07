<?php

namespace App\Filament\Resources\TimeClockEntryResource\Pages;

use App\Filament\Resources\TimeClockEntryResource;
use App\Models\User; // Import User model
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;


class CreateTimeClockEntry extends CreateRecord
{
    protected static string $resource = TimeClockEntryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // A captura de IP e User Agent foi comentada, o que faz sentido
        // se o formulário de criação no admin for para ajustes ou entradas retroativas,
        // onde esses dados não seriam da máquina do admin, mas sim do evento original.
        // Se a intenção fosse registrar uma batida "agora" pelo admin, descomentar faria sentido.
        // $data['ip_address'] = request()->ip();
        // $data['user_agent'] = request()->userAgent();

        // A lógica de company_id é bem tratada pelo afterStateUpdated no formulário
        // e o campo company_id é disabled e dehydrated.
        // Esta parte garante uma camada extra de consistência.
        if (isset($data['user_id'])) {
            $selectedUser = User::find($data['user_id']);
            if ($selectedUser) {
                // Garante que a company_id da batida seja a company_id do usuário selecionado.
                $data['company_id'] = $selectedUser->company_id;
            }
            // Considerar o que fazer se $selectedUser->company_id for nulo,
            // mas o campo company_id no formulário é required.
            // A validação do formulário deve pegar isso se o afterStateUpdated não preencher.
        }

        return $data;
    }
}
