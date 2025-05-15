<?php

namespace App\Filament\Resources\FinancialTransactionResource\Pages;

use App\Filament\Resources\FinancialTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth; // Necessário se você fosse definir company_id aqui

class CreateFinancialTransaction extends CreateRecord
{
    protected static string $resource = FinancialTransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // O company_id é definido automaticamente pelo evento 'creating' no modelo FinancialTransaction.
        // O user_id também é definido automaticamente pelo evento 'creating' no modelo.

        // A manipulação do valor (amount) que você tinha:
        // $data['valor'] = floatval($data['valor'] * 100) / 10000;
        // Geralmente não é necessária com o MoneyInput e o casting 'decimal:2' no modelo.
        // O MoneyInput (Pelmered/FilamentMoneyField) costuma armazenar o valor em centavos (inteiro).
        // Se o seu campo 'amount' no banco de dados é DECIMAL(15,2), o Laravel cuidará da conversão.
        // Se o MoneyInput estiver configurado para enviar o valor já como decimal, a manipulação também é desnecessária.
        // Vamos assumir que o MoneyInput e o cast do Eloquent estão lidando com isso corretamente.
        // Se você tiver problemas com o valor, esta é a área para investigar a formatação do MoneyInput.

        // Se você precisar fazer alguma outra mutação específica antes de criar, adicione aqui.
        // Por exemplo, se o 'amount' viesse como uma string formatada que precisasse ser limpa:
        // if (isset($data['amount']) && is_string($data['amount'])) {
        //     $data['amount'] = (float) str_replace(['.', ','], ['', '.'], $data['amount']);
        // }

        return $data; // Retorna os dados como estão, confiando no MoneyInput e no modelo.
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Lançamento financeiro criado com sucesso!';
    }
}
