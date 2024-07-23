<?php

namespace App\Filament\Clusters\Financeiro\Resources\PlanoDeContaResource\Pages;

use App\Filament\Actions\PlanoDeContasIniciarNovo;
use App\Filament\Clusters\Financeiro\Resources\PlanoDeContaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListPlanoDeContas extends ListRecords
{
    protected static string $resource = PlanoDeContaResource::class;

    public function filterTableQuery(Builder $query): Builder
    {
        return $query->where('plano_de_conta_id', null);
    }

    protected function getHeaderActions(): array
    {
        return [
            PlanoDeContasIniciarNovo::make('iniciar-novo'),
        ];
    }
}
