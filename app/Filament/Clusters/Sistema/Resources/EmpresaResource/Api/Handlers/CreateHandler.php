<?php
namespace App\Filament\Clusters\Sistema\Resources\EmpresaResource\Api\Handlers;

use App\Filament\Clusters\Sistema\Resources\EmpresaResource;
use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = EmpresaResource::class;

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    public function handler(Request $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}
