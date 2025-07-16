<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    public bool $isLoadingCnpj = false;
    public bool $isLoadingCep = false;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    #[On('fetchCnpjCompanyData')]
    public function fetchCnpjCompanyData(string $cnpj): void
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
                    ->title('Erro na Consulta')
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
            $newLatFromApi = $data['estabelecimento']['latitude'] ?? null;
            $newLngFromApi = $data['estabelecimento']['longitude'] ?? null;

            $newData = [
                'social_name' => $data['razao_social'] ?? $this->data['social_name'],
                'name' => $data['nome_fantasia'] ?? $data['razao_social'] ?? $this->data['name'],
                'email' => $data['estabelecimento']['email'] ?? $this->data['email'],
                'phone_number' => $this->formatPhoneNumber($data['estabelecimento'] ?? []) ?? $this->data['phone_number'],
                'address_street' => $data['estabelecimento']['logradouro'] ?? $this->data['address_street'],
                'address_number' => $data['estabelecimento']['numero'] ?? $this->data['address_number'],
                'address_complement' => $data['estabelecimento']['complemento'] ?? $this->data['address_complement'],
                'address_district' => $data['estabelecimento']['bairro'] ?? $this->data['address_district'],
                'address_city' => $data['estabelecimento']['cidade']['nome'] ?? $this->data['address_city'],
                'address_state' => $data['estabelecimento']['estado']['sigla'] ?? $this->data['address_state'],
                'address_zip_code' => preg_replace('/[^0-9]/', '', $data['estabelecimento']['cep'] ?? '') ?: $this->data['address_zip_code'],
                'latitude' => $newLatFromApi ?? $this->data['latitude'],
                'longitude' => $newLngFromApi ?? $this->data['longitude'],
            ];
            $this->form->fill(array_merge($currentFormData, $newData));

            Notification::make()
                ->title('CNPJ Consultado')
                ->body('Dados preenchidos com base na consulta.')
                ->success()
                ->send();

            // Obtém as coordenadas finais que foram preenchidas no formulário
            $filledLat = $this->form->getState()['latitude'] ?? null;
            $filledLng = $this->form->getState()['longitude'] ?? null;

            if ($newLatFromApi && $newLngFromApi) {
                // Se a API do CNPJ forneceu coordenadas, dispara o evento para atualizar o mapa
                $this->dispatch('updateMapLocation', lat: (float)$newLatFromApi, lng: (float)$newLngFromApi, target: 'map_visualization');
            } elseif (empty($filledLat) && empty($filledLng)) {
                // Se, após o preenchimento, o formulário ainda não tiver lat/lng (ou seja, nem a API nem o registro original tinham),
                // tenta geocodificar o endereço.
                $this->geocodeAddressAndFillCoordinates();
            }
            // Se $filledLat e $filledLng estiverem presentes (da API ou do registro original),
            // e não vieram diretamente da API (primeira condição falsa),
            // o campo de mapa reativo já deve ter se atualizado.
            // Um dispatch explícito para este caso poderia ser adicionado se o mapa não estiver centralizando corretamente:
            // elseif ($filledLat && $filledLng) {
            //     $this->dispatch('updateMapLocation', lat: (float)$filledLat, lng: (float)$filledLng, target: 'map_visualization');
            // }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Notification::make()
                ->title('Erro de Conexão')
                ->body('Não foi possível conectar ao serviço de consulta de CNPJ.')
                ->danger()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro na Consulta')
                ->body('Ocorreu um erro inesperado: ' . $e->getMessage())
                ->danger()
                ->send();
            Log::error('Erro na consulta de CNPJ: ' . $e->getMessage(), ['exception' => $e]);
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

    #[On('fetchCompanyCepData')]
    public function fetchCompanyCepData(string $cep): void
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
                'address_street' => $data['logradouro'] ?? $this->data['address_street'],
                'address_complement' => $data['complemento'] ?? $this->data['address_complement'],
                'address_district' => $data['bairro'] ?? $this->data['address_district'],
                'address_city' => $data['localidade'] ?? $this->data['address_city'],
                'address_state' => $data['uf'] ?? $this->data['address_state'],
            ];
            $this->form->fill(array_merge($currentFormData, $newData));

            Notification::make()
                ->title('CEP Consultado')
                ->body('Endereço preenchido com base na consulta do CEP.')
                ->success()
                ->send();

            $this->geocodeAddressAndFillCoordinates();

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Notification::make()
                ->title('Erro de Conexão (CEP)')
                ->body('Não foi possível conectar ao serviço de consulta de CEP.')
                ->danger()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro na Consulta de CEP')
                ->body('Ocorreu um erro inesperado: ' . $e->getMessage())
                ->danger()
                ->send();
            Log::error('Erro na consulta de CEP: ' . $e->getMessage(), ['exception' => $e]);
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
            return;
        }

        $addressParts = array_filter([$street, $number, $district, $city, $state, $zipCode]);
        $fullAddress = implode(', ', $addressParts);

        $apiKey = config('filament-google-maps.key');

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
                'language' => 'pt-BR',
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
                $currentFormData = $this->form->getState();
                $newLat = $location['lat'];
                $newLng = $location['lng'];

                $newData = [
                    'latitude' => $newLat,
                    'longitude' => $newLng,
                ];
                $this->form->fill(array_merge($currentFormData, $newData));
                Notification::make()
                    ->title('Coordenadas Encontradas')
                    ->body('Latitude e Longitude atualizadas com base no endereço.')
                    ->success()
                    ->send();

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
