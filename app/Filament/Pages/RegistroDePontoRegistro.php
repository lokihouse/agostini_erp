<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class RegistroDePontoRegistro extends Page
{
    protected static ?string $title = 'Registro de Ponto :: Registro';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.registro-de-ponto-registro';
    protected ?string $heading = '';
    protected static ?string $slug = 'registro-de-ponto/registro';
    public static function getRelativeRouteName(): string
    {
        return "registro-de-ponto.registro";
    }
}
