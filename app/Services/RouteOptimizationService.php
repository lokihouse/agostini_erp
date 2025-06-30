<?php

namespace App\Services;

use App\Models\TransportOrder;
use App\Models\TransportOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RouteOptimizationService
{
    protected string $apiKey;
    protected const GOOGLE_API_URL = 'https://maps.googleapis.com/maps/api/distancematrix/json';

    public function __construct()
    {
        $this->apiKey = config('services.google.maps_api_key');
        if (empty($this->apiKey)) {
            throw new \Exception('Google Maps API key is not configured.');
        }
    }

    /**
     * Calcula e atualiza a sequência de entrega para todos os itens de uma Ordem de Transporte.
     *
     * @param TransportOrder $order
     * @return bool True se a sequência foi calculada, false em caso de falha.
     */
    public function calculateSequence(TransportOrder $order): bool
    {
        // Carrega os itens com seus clientes e as coordenadas dos clientes
        $order->load('items.client');

        if ($order->items->isEmpty()) {
            // Se não há itens, não há o que fazer.
            return true;
        }

        // 1. Obter o ponto de partida (empresa) e os pontos de entrega (clientes únicos)
        // Assumindo que a empresa está relacionada ao usuário que criou a ordem ou à própria ordem.
        // Adapte esta lógica para buscar o endereço/coordenadas da sua empresa.
        $company = auth()->user()->company; // Exemplo: buscando a empresa do usuário logado
        if (!$company || !$company->latitude || !$company->longitude) {
            Log::error('Empresa sem coordenadas definidas para o cálculo da rota.', ['order_id' => $order->uuid]);
            return false;
        }
        $startPoint = "{$company->latitude},{$company->longitude}";
        $startPoint = "-21,-46";

        // Agrupa os itens por cliente para tratar cada cliente como uma única parada
        $uniqueClients = $order->items->unique('client_id')->pluck('client');

        // Monta a lista de destinos com coordenadas
        $destinations = $uniqueClients->map(function ($client) {
            return "{$client->latitude},{$client->longitude}";
        })->all();

        // Mapeia o client_uuid para a string de coordenada para referência futura
        $clientCoordinateMap = $uniqueClients->mapWithKeys(function ($client) {
            return [$client->uuid => "{$client->latitude},{$client->longitude}"];
        });

        // 2. Implementar o algoritmo "Vizinho Mais Próximo"
        $orderedClientUuids = $this->findNearestNeighborRoute($startPoint, $clientCoordinateMap);

        if (empty($orderedClientUuids)) {
            return false; // Algo deu errado na otimização
        }

        // 3. Atualizar a sequência de entrega no banco de dados
        DB::transaction(function () use ($order, $orderedClientUuids) {
            // Primeiro, zera todas as sequências para evitar conflitos
            $order->items()->update(['delivery_sequence' => null]);

            // Atribui a nova sequência
            foreach ($orderedClientUuids as $index => $clientUuid) {
                $sequence = $index + 1;
                TransportOrderItem::where('transport_order_id', $order->uuid)
                    ->where('client_id', $clientUuid)
                    ->update(['delivery_sequence' => $sequence]);
            }
        });

        return true;
    }

    /**
     * Executa o algoritmo do Vizinho Mais Próximo usando a API do Google.
     *
     * @param string $startPoint Coordenadas do ponto de partida.
     * @param \Illuminate\Support\Collection $clientCoordinateMap Mapa de [client_uuid => 'lat,lng'].
     * @return array Lista ordenada de UUIDs de clientes.
     */
    private function findNearestNeighborRoute(string $startPoint, \Illuminate\Support\Collection $clientCoordinateMap): array
    {
        $route = [];
        $remainingCoordinates = $clientCoordinateMap->toArray();
        $currentPoint = $startPoint;

        while (!empty($remainingCoordinates)) {
            $response = Http::get(self::GOOGLE_API_URL, [
                'origins' => $currentPoint,
                'destinations' => implode('|', $remainingCoordinates),
                'key' => $this->apiKey,
                'mode' => 'driving', // ou 'walking', 'bicycling'
            ]);


            if ($response->failed() || $response->json('status') !== 'OK') {
                Log::error('Google Distance Matrix API error', ['response' => $response->body()]);
                return []; // Retorna rota vazia em caso de erro
            }

            $results = $response->json('rows.0.elements');

            $shortestDuration = PHP_INT_MAX;
            $nextCoordinate = null;
            $nextClientUuid = null;

            foreach ($results as $index => $result) {
                if ($result['status'] === 'OK' && $result['duration']['value'] < $shortestDuration) {
                    $shortestDuration = $result['duration']['value'];
                    // A API retorna os resultados na mesma ordem que enviamos os destinos
                    $nextCoordinate = array_values($remainingCoordinates)[$index];
                    $nextClientUuid = array_keys($remainingCoordinates)[$index];
                }
            }

            if ($nextClientUuid) {
                $route[] = $nextClientUuid;
                $currentPoint = $nextCoordinate;
                unset($remainingCoordinates[$nextClientUuid]);
            } else {
                // Não foi possível encontrar a próxima rota, interrompe para evitar loop infinito
                Log::warning('Não foi possível encontrar o próximo vizinho mais próximo.', ['remaining' => $remainingCoordinates]);
                break;
            }
        }

        return $route;
    }
}

