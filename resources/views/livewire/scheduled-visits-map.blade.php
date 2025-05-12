<div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="fi-section-header-ctn border-b border-gray-200 px-6 py-4 dark:border-white/10">
        <div class="fi-section-header flex flex-col gap-y-2 sm:flex-row sm:items-center">
            <div class="grid flex-1 gap-y-1">
                <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                    Minhas Visitas
                </h3>
                <p class="fi-section-header-description text-sm text-gray-500 dark:text-gray-400">
                    Visualização das próximas visitas.
                </p>
            </div>

            <div class="flex items-center gap-x-3">
                <x-filament::icon-button
                    icon="{{ $viewMode === 'map' ? 'heroicon-o-list-bullet' : 'heroicon-o-map' }}"
                    wire:click="toggleView"
                    color="gray"
                    size="sm"
                    outlined
                />
            </div>
        </div>
    </div>
    <div class="fi-section-content-ctn">
        <div class="fi-section-content p-0">
            @if($viewMode === 'map')
                @if($googleMapsApiKey)
                    @if(!empty($visitsForMap))
                        <div wire:ignore id="scheduledVisitsMapContainer" style="height: 297px; width: 100%;"
                             class="rounded-b-lg"></div>
                    @else
                        <p class="text-center text-gray-500 dark:text-gray-400 p-4">Nenhuma visita agendada com
                            localização definida para exibir no mapa.</p>
                    @endif
                @else
                    <p class="text-center text-red-500 dark:text-red-400 p-4">Chave da API do Google Maps não
                        configurada.</p>
                @endif
            @else
                @if(!empty($visitsForMap))
                    <div class="p-2">
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($visitsForMap as $visit)
                                <li class="py-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $visit['scheduled_at_formatted'] }}</p>
                                            <p class="text-xs font-semibold text-gray-900 dark:text-white">{{ $visit['client_name'] }}</p>
                                        </div>
                                        <div class="flex-shrink-0 ml-4">
                                            <x-filament::icon-button
                                                icon="heroicon-m-chevron-double-right"
                                                href="{{ $visit['edit_url'] }}"
                                                tag="a"
                                                label="Filament"
                                            />
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p class="text-center text-gray-500 dark:text-gray-400 p-4">Nenhuma visita agendada com localização
                        definida para exibir na lista.</p>
                @endif
            @endif
        </div>
    </div>

    @if($googleMapsApiKey && !empty($visitsForMap))
        @push('scripts')
            <script>
                function initScheduledVisitsMap() {
                    const visitsData = @json($visitsForMap);
                    let map;
                    let infoWindow;
                    const bounds = new google.maps.LatLngBounds();

                    if (visitsData.length === 0) {
                        return;
                    }

                    const initialCenter = {
                        lat: visitsData[0].latitude,
                        lng: visitsData[0].longitude
                    };

                    map = new google.maps.Map(document.getElementById('scheduledVisitsMapContainer'), {
                        mapId: "{{ env('GOOGLE_MAPS_API_MAP_ID') }}",
                        center: initialCenter,
                        zoom: 12,
                        mapTypeControl: false,
                        streetViewControl: false,
                    });

                    infoWindow = new google.maps.InfoWindow();

                    visitsData.forEach((visit, index) => {
                        const markerPosition = {lat: visit.latitude, lng: visit.longitude};

                        const marker = new google.maps.marker.AdvancedMarkerElement({
                            position: markerPosition, // Usar a variável
                            map: map,
                            title: visit.client_name + ' - ' + visit.scheduled_at_formatted,
                        });

                        marker.addListener('gmp-click', () => {
                            let content = `
                            <div style="max-width: 250px;">
                                <h4 style="font-weight: bold; margin-bottom: 5px;">${visit.client_name}</h4>
                                <p style="font-size: 0.85em; margin-bottom: 3px;"><strong>Fantasia:</strong> ${visit.client_social_name || visit.client_name}</p>
                                <p style="font-size: 0.85em; margin-bottom: 3px;"><strong>Agendado para:</strong> ${visit.scheduled_at_formatted}</p>
                                <p style="font-size: 0.85em; margin-bottom: 3px;"><strong>Endereço:</strong> ${visit.address || 'N/A'}</p>
                                <p style="font-size: 0.85em; margin-bottom: 3px;"><strong>Status:</strong> ${visit.status}</p>
                                <a href="${visit.edit_url}" target="_blank" style="font-size: 0.85em; color: #3b82f6; text-decoration: underline;">Ver Detalhes da Visita</a>
                            </div>
                        `;
                            infoWindow.setContent(content);
                            infoWindow.open(map, marker);
                        });

                        bounds.extend(markerPosition);
                    });

                    if (visitsData.length > 0) {
                        map.fitBounds(bounds);
                        // Se houver apenas um marcador, o fitBounds pode dar um zoom muito alto.
                        if (visitsData.length === 1) {
                            map.setZoom(15); // Ajuste o zoom para um único marcador
                        }
                    }
                }
            </script>
            <script
                src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&loading=async&libraries=marker&mapId={{ env('GOOGLE_MAPS_API_MAP_ID') }}&callback=initScheduledVisitsMap"></script>
        @endpush
    @endif
</div>
