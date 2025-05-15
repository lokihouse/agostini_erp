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
    public string $viewMode = 'map';

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

        $today = Carbon::today();
        $sevenDaysFromNow = $today->copy()->addDays(7);

        $visits = SalesVisit::with([
            'client' => function ($query) {
                $query->whereNotNull('latitude')->whereNotNull('longitude');
            }
        ])
            ->where('assigned_to_user_id', $user->uuid)
            ->whereIn('status', [SalesVisit::STATUS_SCHEDULED, SalesVisit::STATUS_IN_PROGRESS])
            ->orderBy('scheduled_at', 'asc')
            ->get();

        $this->visitsForMap = $visits->filter(function ($visit) {
            return $visit->client && $visit->client->latitude && $visit->client->longitude;
        })->map(function (SalesVisit $visit) use ($today, $sevenDaysFromNow){
            $scheduledAt = Carbon::parse($visit->scheduled_at);
            $markerCategory = 'default';

            if($visit->status === SalesVisit::STATUS_IN_PROGRESS){
                $markerCategory = 'warning';
            } elseif($visit->status === SalesVisit::STATUS_SCHEDULED){
                if($scheduledAt->isPast() || $scheduledAt->isToday()){
                    $markerCategory = 'danger';
                } elseif ($scheduledAt->gt($sevenDaysFromNow)){
                    $markerCategory = 'gray';
                }
            }

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
                'scheduled_at_formatted' => $scheduledAt->format('d/m/Y H:i'),
                'latitude' => (float) $visit->client->latitude,
                'longitude' => (float) $visit->client->longitude,
                'status' => SalesVisit::getStatusOptions()[$visit->status] ?? $visit->status,
                'status_key' => $visit->status, // Adiciona a chave do status para lógica
                'marker_category' => $markerCategory, // Nova chave para a cor/tipo do marcador
                'edit_url' => route('filament.app.pages.processar-visita', ['visit_uuid' => $visit->uuid])
            ];
        })->values()->all();

    }

    public function toggleView(): void
    {
        $this->viewMode = ($this->viewMode === 'map') ? 'list' : 'map';
    }
    public function render()
    {
        return view('livewire.scheduled-visits-map');
    }
}
