<?php

namespace App\Filament\Clusters\Financeiro\Resources\MovimentacaoFinanceiraResource\Pages;

use App\Filament\Clusters\Financeiro\Resources\MovimentacaoFinanceiraResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMovimentacaoFinanceira extends CreateRecord
{
    protected static string $resource = MovimentacaoFinanceiraResource::class;
}
