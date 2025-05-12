<?php
namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class CnpjApiException extends \Exception {}
class CnpjNotFoundException extends \Exception {}

class CnpjService
{
    protected string $baseUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.cnpj_ws.url', 'https://publica.cnpj.ws/cnpj'), '/');
        $this->timeout = config('services.cnpj_ws.timeout', 10);
    }

    /**
     * @param string $cnpj
     * @return array
     * @throws CnpjNotFoundException
     * @throws CnpjApiException
     * @throws ConnectionException
     */
    public function fetchCnpj(string $cnpj): array
    {
        $response = Http::timeout($this->timeout)->get("{$this->baseUrl}/{$cnpj}");

        if ($response->failed()) {
            $this->handleFailedResponse($response, $cnpj);
        }

        $data = $response->json();

        if (isset($data['status']) && (int)$data['status'] === 404) {
            throw new CnpjNotFoundException($data['titulo'] ?? 'O CNPJ informado não foi encontrado ou é inválido.');
        }

        if (!isset($data['estabelecimento'])) {
            $data['estabelecimento'] = [];
        }

        if (!isset($data['estabelecimento']['cidade'])) {
            $data['estabelecimento']['cidade'] = ['nome' => null];
        }
        if (!isset($data['estabelecimento']['estado'])) {
            $data['estabelecimento']['estado'] = ['sigla' => null];
        }

        return $data;
    }

    /**
     * @throws CnpjApiException
     * @throws CnpjNotFoundException
     */
    protected function handleFailedResponse(Response $response, string $cnpj): void
    {
        $status = $response->status();
        $errorMessage = "Falha ao consultar o CNPJ {$cnpj} (HTTP {$status}).";

        if ($status === 404) {
            throw new CnpjNotFoundException("CNPJ {$cnpj} não encontrado na base de dados.");
        } elseif ($status === 429) {
            throw new CnpjApiException("Muitas solicitações para o CNPJ {$cnpj}. Aguarde e tente novamente.");
        }

        $details = $response->json('detalhes');
        if ($details) {
            throw new CnpjApiException($details);
        }

        throw new CnpjApiException($errorMessage);
    }
}
