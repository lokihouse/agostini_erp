<?php

namespace App\Filament\Pages;

use App\Models\RegistroDePonto;
use Filament\Pages\Page;

class RegistroPontoRecibo extends Page
{
    protected static ?string $title = 'Registro de Ponto :: Recibo';
    protected static bool $shouldRegisterNavigation = false;
    protected ?string $heading = '';
    protected static ?string $slug = 'registro-de-ponto/recibo/{id}';
    protected static string $view = 'filament.pages.registro-ponto-recibo';
    public static function getRelativeRouteName(): string
    {
        return "registro-de-ponto.recibo";
    }

    public RegistroDePonto $record;

    public function mount($id): void
    {
        $this->record = (new RegistroDePonto())->FindOrFail($id);
    }
}
