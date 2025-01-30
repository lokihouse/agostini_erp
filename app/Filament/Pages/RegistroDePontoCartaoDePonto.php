<?php

namespace App\Filament\Pages;

use App\Utils\Cnpj;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class RegistroDePontoCartaoDePonto extends Page
{
    protected static ?string $title = 'Registro de Ponto :: Cartão de Ponto';
    protected static bool $shouldRegisterNavigation = false;
    protected ?string $heading = '';
    protected static ?string $slug = 'registro-de-ponto/cartao-de-ponto';
    protected static string $view = 'filament.pages.registro-de-ponto-cartao-de-ponto';

    public static function getRelativeRouteName(): string
    {
        return "registro-de-ponto.cartao-de-ponto";
    }
}
