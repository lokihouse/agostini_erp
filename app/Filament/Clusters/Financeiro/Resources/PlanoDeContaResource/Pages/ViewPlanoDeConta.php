<?php

namespace App\Filament\Clusters\Financeiro\Resources\PlanoDeContaResource\Pages;

use App\Filament\Actions\PlanoDeContaCriarNovoSubitem;
use App\Filament\Clusters\Financeiro\Resources\PlanoDeContaResource;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewPlanoDeConta extends ViewRecord
{
    protected static string $resource = PlanoDeContaResource::class;
    protected static string $view = 'filament.clusters.financeiro.resources.plano-de-conta-resource.view-plano-de-conta';

    /**
     * @return string|Htmlable
     */
    public function getHeading(): string|Htmlable
    {
        return $this->getRecord()->descricao;
    }

    protected function getHeaderActions(): array
    {
        return [
            PlanoDeContaCriarNovoSubitem::make('Novo Subitem')
        ];
    }
}
