<?php

namespace App\Filament\Clusters\Financeiro\Resources\MovimentacaoFinanceiraResource\Pages;

use App\Filament\Actions\MovimentacaoFinanceiraCriarNova;
use App\Filament\Clusters\Financeiro\Resources\MovimentacaoFinanceiraResource;
use App\Models\PlanoDeConta;
use Filament\Actions;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;

class ListMovimentacaoFinanceiras extends ListRecords
{
    protected static string $resource = MovimentacaoFinanceiraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            MovimentacaoFinanceiraCriarNova::make('Criar Movimentação Financeira')
        ];
    }
}
