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

class EmpresaLocalizarCnpj extends Action
{
    protected string | Htmlable | Closure | null $label = 'Localizar CNPJ';
    protected string | Htmlable | Closure | null $icon = 'heroicon-o-magnifying-glass';

    protected function setUp(): void
    {
        parent::setUp();
        $this->action(function (Get $get, Set $set, $state) {
            $state = TextHelper::clear($state);
            if(empty($state)) return;
            $response = Http::get("https://receitaws.com.br/v1/cnpj/{$state}");
            if($response->json()['status'] === 'ERROR'){
                Notification::make()
                    ->title('CNPJ não encontrado')
                    ->danger()
                    ->send();
                return;
            }
            $set('razao_social', $response->json()['nome']);
            $set('nome_fantasia', $response->json()['fantasia']);
            $set('email', $response->json()['email']);
            $set('telefone', TextHelper::toFormatedTelephone($response->json()['telefone']));
            $set('cep', $response->json()['cep']);
            $set('logradouro', $response->json()['logradouro']);
            $set('complemento', $response->json()['complemento']);
            $set('bairro', $response->json()['bairro']);
            $set('municipio', $response->json()['municipio']);
            $set('uf', $response->json()['uf']);
        });
    }
}
