<?php

namespace App\Filament\Actions\Form;

use App\Utils\TextHelper;
use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Http;
use function PHPUnit\Framework\isEmpty;

class EmpresaLocalizarCep extends Action
{
    protected string | Htmlable | Closure | null $label = 'Localizar CEP';
    protected string | Htmlable | Closure | null $icon = 'heroicon-o-magnifying-glass';

    protected function setUp(): void
    {
        parent::setUp();
        $this->action(function (Get $get, Set $set, $state) {
            $state = TextHelper::clear($state);
            if(empty($state)) return;
            $response = Http::get("https://viacep.com.br/ws/{$state}/json/");
            if(isset($response->json()['erro']) && $response->json()['erro']){
                Notification::make()
                    ->title('CEP não encontrado')
                    ->danger()
                    ->send();
                return;
            }

            $logradouro = $response->json()['logradouro'];
            $complemento = $response->json()['complemento'];
            $bairro = $response->json()['bairro'];
            $municipio = $response->json()['localidade'];
            $uf = $response->json()['uf'];

            $set('logradouro', $logradouro);
            // $set('numero', null);
            $set('complemento', $complemento);
            $set('bairro', $bairro);
            $set('municipio', $municipio);
            $set('uf', $uf);

            $numero = $get('numero');

            $response = Http::get("https://maps.googleapis.com/maps/api/geocode/json", [
                'address' => "$logradouro, $numero, $bairro, $municipio, $uf",
                'key' => env('GOOGLE_MAPS_API_KEY'),
            ]);

            if($response->json()['status'] === 'REQUEST_DENIED'){
                Notification::make()
                    ->title('API do Google Maps não configurada')
                    ->danger()
                    ->send();
                return;
            }

            $lat = $response->json()['results'][0]['geometry']['location']['lat'];
            $lng = $response->json()['results'][0]['geometry']['location']['lng'];

            $set('latitude', $lat);
            $set('longitude', $lng);
            $set('localizacao', [
                'lat' => floatval($lat),
                'lng' => floatVal($lng),
            ]);
        });
    }
}
