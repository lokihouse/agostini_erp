<?php

namespace App\Filament\Clusters\Vendas\Resources\VisitaResource\Pages;

use App\Filament\Clusters\Vendas\Resources\VisitaResource;
use Filament\Resources\Pages\Page;

class CheckInVisita extends Page
{
    protected static string $resource = VisitaResource::class;

    protected static string $view = 'filament.clusters.vendas.resources.visita-resource.pages.check-in-visita';
}
