<?php

namespace App\Livewire;

use App\Models\SalesVisit;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Carbon\Carbon;

class ScheduledVisitsMap extends Component
{
    public array $visitsForMap = [];
    public ?string $googleMapsApiKey;

    public function mount(): void
    {
        $this->googleMapsApiKey = config('filament-google-maps.key');
        $this->loadScheduledVisits();
    }

    public function loadScheduledVisits(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->visitsForMap = [];
            return;
        }

        // Buscar visitas agendadas para o usuário, de hoje em diante,
        // que não estão canceladas ou concluídas, e cujo cliente tem lat/lng.
        $visits = SalesVisit::with([
            'client' => function ($query) {
                $query->whereNotNull('latitude')->whereNotNull('longitude');
            }
        ])
            ->where('assigned_to_user_id', $user->uuid)
            ->where('status', SalesVisit::STATUS_SCHEDULED)
            // ->whereDate('scheduled_at', '>=', Carbon::today()) // Apenas de hoje em diante
            // Ou, para um período específico, ex: próximos 7 dias
            ->whereBetween('scheduled_at', [Carbon::today()->startOfDay(), Carbon::today()->addDays(7)->endOfDay()])
            ->orderBy('scheduled_at', 'asc')
            ->get();

        $this->visitsForMap = $visits->filter(function ($visit) {
            // Garante que o cliente foi carregado e tem coordenadas
            return $visit->client && $visit->client->latitude && $visit->client->longitude;
        })->map(function (SalesVisit $visit) {
            return [
                'id' => $visit->uuid,
                'client_name' => $visit->client->name,
                'client_social_name' => $visit->client->social_name,
                'address' => trim(implode(', ', array_filter([
                    $visit->client->address_street,
                    $visit->client->address_number,
                    $visit->client->address_district,
                    $visit->client->address_city,
                    $visit->client->address_state,
                ]))),
                'scheduled_at_formatted' => Carbon::parse($visit->scheduled_at)->format('d/m/Y H:i'),
                'latitude' => (float) $visit->client->latitude,
                'longitude' => (float) $visit->client->longitude,
                'status' => SalesVisit::getStatusOptions()[$visit->status] ?? $visit->status,
                // Link para editar a visita (opcional)
                'edit_url' => route('filament.app.resources.sales-visits.edit', ['record' => $visit->uuid]),
            ];
        })->values()->all(); // values() para reindexar o array após o filter
    }

    public function render()
    {
        return view('livewire.scheduled-visits-map');
    }
}
