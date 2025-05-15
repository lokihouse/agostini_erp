<?php

namespace App\Filament\Resources\FinancialTransactionResource\Pages;

use App\Filament\Resources\FinancialTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFinancialTransactions extends ListRecords
{
    protected static string $resource = FinancialTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
