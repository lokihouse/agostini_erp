<?php

namespace App\Livewire;

use App\Http\Controllers\VisitaController;
use App\Models\Visita;
use Cheesegrits\FilamentGoogleMaps\Widgets\MapWidget;
use Illuminate\Support\Carbon;

class VisitasDiasMapa extends MapWidget
{
    protected static ?string $heading = null;
    protected static ?string $icon = null;
    protected static ?bool $clustering = true;

    public function getMapConfig(): string
    {
        $config = json_decode(parent::getMapConfig(), true);
        $config['zoom'] = 3;
        $config['center'] = [
            'lat' => -15.793889,
            'lng' => -47.882778,
        ];
        return json_encode($config);
    }

    protected function getData(): array
    {
        $data = [];

        $visitas = app(VisitaController::class)
            ->proximosDias()
            ->get()
            ->toArray();

        foreach ($visitas as $visita)
        {
            $data[] = [
                'location'  => $visita['cliente']['localizacao'],
                'label' => $visita['cliente']['nome_fantasia'],
            ];
        }

        return array_values(collect($data)->unique()->toArray());
    }
}
