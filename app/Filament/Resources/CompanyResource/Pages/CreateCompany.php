<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    public bool $isLoadingCnpj = false;
    public bool $isLoadingCep = false;

    #[On('fetchCnpjData')]
    public function fetchCnpjData(string $cnpj): void
    {
        if (empty($cnpj)) {
            Notification::make()
                ->title('CNPJ não informado')
                ->warning()
                ->send();
            return;
        }

        try {
            $this->isLoadingCnpj = true;
            $response = Http::timeout(10)->get("https://publica.cnpj.ws/cnpj/{$cnpj}");

            if ($response->failed()) {
                $status = $response->status();
                $errorMessage = "Falha ao consultar o CNPJ (HTTP {$status}).";
                if ($status === 404) {
                    $errorMessage = "CNPJ não encontrado na base de dados.";
                } elseif ($status === 429) {
                    $errorMessage = "Muitas solicitações. Aguarde um momento e tente novamente.";
                } elseif ($response->json('detalhes')) {
                    $errorMessage = $response->json('detalhes');
                }

                Notification::make()
                    ->title('Erro na Consulta de CNPJ')
                    ->body($errorMessage)
                    ->danger()
                    ->send();
                return;
            }

            $data = $response->json();

            if (isset($data['status']) && $data['status'] == 404) {
                Notification::make()
                    ->title('CNPJ Inválido ou Não Encontrado')
                    ->body($data['titulo'] ?? 'O CNPJ informado não foi encontrado ou é inválido.')
                    ->warning()
                    ->send();
                return;
            }

            $currentFormData = $this->form->getState();
            $newLat = $data['estabelecimento']['latitude'] ?? null;
            $newLng = $data['estabelecimento']['longitude'] ?? null;

            $newData = [
                'social_name' => $data['razao_social'] ?? null,
                'tax_number' => $data['estabelecimento']['cnpj'] ?? $cnpj,
                'name' => $data['nome_fantasia'] ?? $data['razao_social'] ?? null,
                'email' => $data['estabelecimento']['email'] ?? null,
                'phone_number' => $this->formatPhoneNumber($data['estabelecimento'] ?? []),
                'address_street' => $data['estabelecimento']['logradouro'] ?? null,
                'address_number' => $data['estabelecimento']['numero'] ?? null,
                'address_complement' => $data['estabelecimento']['complemento'] ?? null,
                'address_district' => $data['estabelecimento']['bairro'] ?? null,
                'address_city' => $data['estabelecimento']['cidade']['nome'] ?? null,
                'address_state' => $data['estabelecimento']['estado']['sigla'] ?? null,
                'address_zip_code' => preg_replace('/[^0-9]/', '', $data['estabelecimento']['cep'] ?? ''),
                'latitude' => $newLat, // Usar as variáveis
                'longitude' => $newLng, // Usar as variáveis
            ];
            $this->form->fill(array_merge($currentFormData, $newData));

            Notification::make()
                ->title('CNPJ Consultado')
                ->body('Dados preenchidos com base na consulta.')
                ->success()
                ->send();

            if ($newLat && $newLng) {
                $this->dispatch('updateMapLocation', lat: (float)$newLat, lng: (float)$newLng, target: 'map_visualization');
            } elseif (empty($newLat) || empty($newLng)) {
                $this->geocodeAddressAndFillCoordinates();
            }


        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Notification::make()
                ->title('Erro de Conexão (CNPJ)')
                ->body('Não foi possível conectar ao serviço de consulta de CNPJ.')
                ->danger()
                ->send();
        } catch (\Exception $e) {
            Log::error('Erro na consulta de CNPJ: ' . $e->getMessage(), ['exception' => $e]);
            Notification::make()
                ->title('Erro na Consulta de CNPJ')
                ->body('Ocorreu um erro inesperado. Consulte os logs para mais detalhes.')
                ->danger()
                ->send();
        } finally {
            $this->isLoadingCnpj = false;
        }
    }

    protected function formatPhoneNumber(array $estabelecimentoData): ?string
    {
        $ddd = $estabelecimentoData['ddd1'] ?? $estabelecimentoData['ddd'] ?? null;
        $phone = $estabelecimentoData['telefone1'] ?? $estabelecimentoData['telefone'] ?? null;

        if ($ddd && $phone) {
            return preg_replace('/[^0-9]/', '', $ddd . $phone);
        }
        return null;
    }

    #[On('fetchCepData')]
    public function fetchCepData(string $cep): void
    {
        if (empty($cep)) {
            Notification::make()
                ->title('CEP não informado')
                ->warning()
                ->send();
            return;
        }

        try {
            $this->isLoadingCep = true;
            $response = Http::timeout(5)->get("https://viacep.com.br/ws/{$cep}/json/");

            if ($response->failed()) {
                Notification::make()
                    ->title('Erro na Consulta de CEP')
                    ->body("Falha ao consultar o CEP (HTTP {$response->status()}).")
                    ->danger()
                    ->send();
                return;
            }

            $data = $response->json();

            if (isset($data['erro']) && $data['erro'] === true) {
                Notification::make()
                    ->title('CEP Não Encontrado')
                    ->body('O CEP informado não foi encontrado na base de dados.')
                    ->warning()
                    ->send();
                return;
            }

            $currentFormData = $this->form->getState();
            $newData = [
                'address_street' => $data['logradouro'] ?? null,
                'address_complement' => $data['complemento'] ?? null,
                'address_district' => $data['bairro'] ?? null,
                'address_city' => $data['localidade'] ?? null,
                'address_state' => $data['uf'] ?? null,
                // address_zip_code já foi preenchido pelo usuário
            ];
            $this->form->fill(array_merge($currentFormData, $newData));

            Notification::make()
                ->title('CEP Consultado')
                ->body('Endereço preenchido com base na consulta do CEP.')
                ->success()
                ->send();

            // Tentar geocodificar o endereço APÓS preencher com dados do CEP
            $this->geocodeAddressAndFillCoordinates();

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Notification::make()
                ->title('Erro de Conexão (CEP)')
                ->body('Não foi possível conectar ao serviço de consulta de CEP.')
                ->danger()
                ->send();
        } catch (\Exception $e) {
            Log::error('Erro na consulta de CEP: ' . $e->getMessage(), ['exception' => $e]);
            Notification::make()
                ->title('Erro na Consulta de CEP')
                ->body('Ocorreu um erro inesperado. Consulte os logs para mais detalhes.')
                ->danger()
                ->send();
        } finally {
            $this->isLoadingCep = false;
        }
    }
    protected function geocodeAddressAndFillCoordinates(): void
    {
        $formData = $this->form->getState();

        $street = $formData['address_street'] ?? '';
        $number = $formData['address_number'] ?? '';
        $district = $formData['address_district'] ?? '';
        $city = $formData['address_city'] ?? '';
        $state = $formData['address_state'] ?? '';
        $zipCode = preg_replace('/[^0-9]/', '', $formData['address_zip_code'] ?? '');

        if (empty($street) || empty($city) || empty($state)) {
            // Não tentar geocodificar se informações básicas do endereço estiverem faltando
            // Notification::make()
            //     ->title('Endereço Incompleto')
            //     ->body('Não foi possível buscar coordenadas: endereço principal incompleto.')
            //     ->info()
            //     ->send();
            return;
        }

        $addressParts = array_filter([$street, $number, $district, $city, $state, $zipCode]);
        $fullAddress = implode(', ', $addressParts);

        $apiKey = config('filament-google-maps.key'); // Pega a chave do config do pacote

        if (empty($apiKey)) {
            Notification::make()
                ->title('Chave da API Google Maps Ausente')
                ->body('A chave da API do Google Maps não está configurada para geocodificação.')
                ->danger()
                ->send();
            Log::warning('Tentativa de geocodificação sem API Key do Google Maps.');
            return;
        }

        try {
            $response = Http::timeout(10)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $fullAddress,
                'key' => $apiKey,
                'language' => 'pt-BR', // Opcional: para resultados em português
            ]);

            if ($response->failed()) {
                Notification::make()
                    ->title('Erro na Geocodificação')
                    ->body("Falha ao buscar coordenadas (HTTP {$response->status()}).")
                    ->danger()
                    ->send();
                return;
            }

            $geoData = $response->json();

            if ($geoData['status'] === 'OK' && !empty($geoData['results'][0]['geometry']['location'])) {
                $location = $geoData['results'][0]['geometry']['location'];
                $currentFormData = $this->form->getState(); // Pegar o estado atual ANTES de modificar lat/lng

                $newLat = $location['lat'];
                $newLng = $location['lng'];

                $newData = [
                    'latitude' => $newLat,
                    'longitude' => $newLng,
                ];
                $this->form->fill(array_merge($currentFormData, $newData)); // Mescla apenas lat/lng com o estado atual

                Notification::make()
                    ->title('Coordenadas Encontradas')
                    ->body('Latitude e Longitude atualizadas com base no endereço.')
                    ->success()
                    ->send();

                // Dispara um evento para o JavaScript do mapa atualizar sua visualização
                $this->dispatch('updateMapLocation', lat: (float)$newLat, lng: (float)$newLng, target: 'map_visualization');

            } elseif ($geoData['status'] === 'ZERO_RESULTS') {
                Notification::make()
                    ->title('Coordenadas Não Encontradas')
                    ->body('Não foi possível encontrar coordenadas para o endereço fornecido.')
                    ->warning()
                    ->send();
            } else {
                Notification::make()
                    ->title('Erro na Geocodificação')
                    ->body("Resposta inesperada do serviço de geocodificação: " . ($geoData['error_message'] ?? $geoData['status']))
                    ->danger()
                    ->send();
                Log::warning('Erro na geocodificação', ['status' => $geoData['status'], 'response' => $geoData]);
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Notification::make()
                ->title('Erro de Conexão (Geocodificação)')
                ->body('Não foi possível conectar ao serviço de geocodificação do Google Maps.')
                ->danger()
                ->send();
        } catch (\Exception $e) {
            Log::error('Erro na geocodificação: ' . $e->getMessage(), ['exception' => $e]);
            Notification::make()
                ->title('Erro na Geocodificação')
                ->body('Ocorreu um erro inesperado ao buscar coordenadas. Consulte os logs.')
                ->danger()
                ->send();
        }
    }
}
