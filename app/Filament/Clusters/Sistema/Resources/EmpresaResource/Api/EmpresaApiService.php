<?php
namespace App\Filament\Clusters\Sistema\Resources\EmpresaResource\Api;

use App\Filament\Clusters\Sistema\Resources\EmpresaResource;
use Rupadana\ApiService\ApiService;


class EmpresaApiService extends ApiService
{
    protected static string | null $resource = EmpresaResource::class;

    public static function handlers() : array
    {
        return [
            \App\Filament\Clusters\Sistema\Resources\EmpresaResource\Api\Handlers\CreateHandler::class,
            \App\Filament\Clusters\Sistema\Resources\EmpresaResource\Api\Handlers\UpdateHandler::class,
            \App\Filament\Clusters\Sistema\Resources\EmpresaResource\Api\Handlers\DeleteHandler::class,
            EmpresaResource\Api\Handlers\PaginationHandler::class,
            \App\Filament\Clusters\Sistema\Resources\EmpresaResource\Api\Handlers\DetailHandler::class
        ];

    }
}
