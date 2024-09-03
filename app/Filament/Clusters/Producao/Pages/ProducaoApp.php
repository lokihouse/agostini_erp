<?php

namespace App\Filament\Clusters\Producao\Pages;

use App\Filament\Clusters\Producao;
use Filament\Pages\Page;
use Filament\Support\RawJs;

class ProducaoApp extends Page
{
    protected ?string $heading = 'App';
    protected static ?string $title = 'Produção - App';
    protected static ?string $navigationLabel = 'App';
    protected static ?int $navigationSort = 999;
    protected static ?string $navigationIcon = 'fas-mobile';
    protected static string $view = 'filament.clusters.producao.pages.producao-app';
    protected static ?string $cluster = Producao::class;

    public bool $cameraAberta = false;

    public function toggleCamera()
    {
        $this->cameraAberta = !$this->cameraAberta;
    }

    public function openModal($id)
    {
        $this->dispatch('open-modal', id: 'modal_op_' . $id);
    }

    public function closeModal($id)
    {
        $this->dispatch('close-modal', id: 'modal_op_' . $id);
    }
}
