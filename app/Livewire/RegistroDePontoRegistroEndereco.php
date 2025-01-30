<?php

namespace App\Livewire;

use App\Models\RegistroDePonto;
use http\Env;
use Livewire\Component;

class RegistroDePontoRegistroEndereco extends Component
{
    public $tipo = null;
    public $latitude = null;
    public $longitude = null;
    public $accuracy = null;

    public $old_latitude = null;
    public $old_longitude = null;
    public $old_accuracy = null;

    public $googleRequestStatus = null;
    public $address = null;

    public function render()
    {
        return view('livewire.registro-de-ponto-registro-endereco');
    }

    public function registrarPonto()
    {
        $registroDePonto = new RegistroDePonto();
        $registroDePonto->user_id = auth()->user()->id;
        $registroDePonto->data = date('Y-m-d H:i:s');
        $registroDePonto->tipo = $this->tipo;
        $registroDePonto->ip = request()->ip();
        $registroDePonto->device_id = request()->userAgent();
        $registroDePonto->latitude = $this->latitude;
        $registroDePonto->longitude = $this->longitude;
        $registroDePonto->accuracy = $this->accuracy;
        $registroDePonto->address = $this->address;
        $registroDePonto->hash = hash('sha256', json_encode($registroDePonto));
        $registroDePonto->save();

        $this->redirect(route('filament.app.pages.registro-de-ponto.recibo', $registroDePonto->id));
    }
}
