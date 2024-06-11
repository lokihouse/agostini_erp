<?php
namespace App\Filament\Clusters\Sistema\Resources\EmpresaResource\Api\Handlers;

use App\Filament\Clusters\Sistema\Resources\EmpresaResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;

class PaginationHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = EmpresaResource::class;

    public function handler()
    {
        $query = static::getEloquentQuery();
        $model = static::getModel();

        $query = QueryBuilder::for($query)
        ->allowedFields($model::$allowedFields ?? [])
        ->allowedSorts($model::$allowedSorts ?? [])
        ->allowedFilters($model::$allowedFilters ?? [])
        ->allowedIncludes($model::$allowedIncludes ?? null)
        ->paginate(request()->query('per_page'))
        ->appends(request()->query());

        return static::getApiTransformer()::collection($query);
    }
}
