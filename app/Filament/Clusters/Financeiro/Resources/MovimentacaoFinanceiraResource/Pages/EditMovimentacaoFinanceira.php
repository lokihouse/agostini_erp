<?php

namespace App\Filament\Clusters\Financeiro\Resources\MovimentacaoFinanceiraResource\Pages;

use App\Filament\Clusters\Financeiro\Resources\MovimentacaoFinanceiraResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMovimentacaoFinanceira extends EditRecord
{
    protected static string $resource = MovimentacaoFinanceiraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
