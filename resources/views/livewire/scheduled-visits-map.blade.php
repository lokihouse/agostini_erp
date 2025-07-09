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
                {{-- Botão de alternância atualizado --}}
                <x-filament::icon-button
                    icon="{{ $viewMode === 'map' ? 'heroicon-o-list-bullet' : 'heroicon-o-map' }}"
                    wire:click="toggleView"
                    color="gray"
                    size="sm"
                    label="{{ $viewMode === 'map' ? 'Ver Lista' : 'Ver Mapa' }}"
                    tooltip="{{ $viewMode === 'map' ? 'Alternar para visualização em lista' : 'Alternar para visualização em mapa' }}"
                />
            </div>
        </div>
    </div>
    <div class="fi-section-content-ctn">
        <div class="fi-section-content p-0">
            <div @if($viewMode !== 'map') class="hidden" @endif>
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
            </div>
            <div @if($viewMode !== 'list') class="hidden" @endif>
                {{-- Modo Lista --}}
                @if(!empty($visitsForMap))
                    <div class="p-2 max-h-[297px] overflow-y-auto"> {{-- Adicionado max-height e overflow --}}
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($visitsForMap as $visit)
                                @php
                                    $markerColor = "gray";
                                    switch($visit['marker_category']){
                                        case 'danger':
                                            $markerColor = "red";
                                            break;
                                        case 'warning':
                                            $markerColor = "#FFA500";
                                            break;
                                    }
                                @endphp
                                <li class="py-3 px-2 border-s-8" style="border-color: {{ $markerColor }};"> {{-- Adicionado px-2 para consistência --}}
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $visit['scheduled_at_formatted'] }}
                                                @if($visit['marker_category'] === 'danger')
                                                    <span class="ml-1 text-red-500 font-semibold">(Atrasada/Hoje)</span>
                                                @elseif($visit['marker_category'] === 'warning')
                                                    <span class="ml-1 text-yellow-500 font-semibold">(Em Andamento)</span>
                                                @elseif($visit['marker_category'] === 'gray')
                                                    <span class="ml-1 text-gray-400 font-semibold">(Distante)</span>
                                                @endif
                                            </div>
                                            <p class="text-xs font-semibold text-gray-900 dark:text-white">{{ $visit['client_name'] }}</p>
                                            <div class="text-[9px] text-gray-500 dark:text-gray-400 truncate" title="{{ $visit['address'] }}">{{ $visit['address'] }}</div>
                                        </div>
                                        <div class="flex-shrink-0 ml-4">
                                            {{-- Botão de detalhes atualizado --}}
                                            <x-filament::icon-button
                                                icon="heroicon-m-chevron-double-right"
                                                href="{{ $visit['edit_url'] }}"
                                                tag="a"
                                                label="Ver Detalhes"
                                                tooltip="Ver detalhes da visita"
                                                size="sm"
                                                color="gray"
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
            </div>
        </div>
    </div>

    {{-- Script do Google Maps só é carregado se o modo mapa estiver ativo e houver dados --}}
    @if($googleMapsApiKey && !empty($visitsForMap) && $viewMode === 'map')
        @push('scripts')
            <script>
                async function initScheduledVisitsMap() {
                    const visitsData = @json($visitsForMap);
                    let map;
                    let infoWindow;
                    const bounds = new google.maps.LatLngBounds();

                    if (visitsData.length === 0) {
                        return;
                    }

                    const {PinElement} = await google.maps.importLibrary("marker");

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

                    // CORREÇÃO: de visitsData para visitsData
                    visitsData.forEach((visit, index) => {
                        const markerPosition = {lat: visit.latitude, lng: visit.longitude};
                        let pinGlyphElement, pinBackground, pinBorderColor; // Renomeado pinGlyph para pinGlyphElement para clareza

                        switch (visit.marker_category) {
                            case 'danger': // Atrasadas (Vermelho)
                                pinBackground = '#FF0000'; // Vermelho
                                pinBorderColor = '#A00000'; // Vermelho escuro
                                pinGlyphElement = new PinElement({
                                    glyph: "!",
                                    glyphColor: "white",
                                    background: pinBackground,
                                    borderColor: pinBorderColor,
                                    scale: 1,
                                }).element;
                                break;
                            case 'warning': // Em Andamento (Amarelo)
                                pinBackground = '#FFA500'; // Laranja/Amarelo
                                pinBorderColor = '#CC8400';
                                pinGlyphElement = new PinElement({
                                    glyphColor: "#CC8400", // Melhor contraste com amarelo
                                    background: pinBackground,
                                    borderColor: pinBorderColor,
                                    scale: 1,
                                }).element;
                                break;
                            default: // Mais de 7 dias (Cinza)
                                pinBackground = '#DDDDDD'; // Cinza
                                pinBorderColor = '#000000';
                                pinGlyphElement = new PinElement({
                                    glyph: "",
                                    glyphColor: "white",
                                    background: pinBackground,
                                    borderColor: pinBorderColor,
                                    scale: 0.6, // Um pouco menor
                                }).element;
                                break;
                        }

                        const marker = new google.maps.marker.AdvancedMarkerElement({
                            position: markerPosition,
                            map: map,
                            title: visit.client_name + ' - ' + visit.scheduled_at_formatted,
                            content: pinGlyphElement, // Usa o elemento DOM do PinElement
                        });

                        marker.addListener('gmp-click', () => {
                            let content = `
                            <div style="max-width: 250px;">
                                <h4 style="font-weight: bold; margin-bottom: 5px;">${visit.client_name}</h4>
                                <p style="font-size: 0.85em; margin-bottom: 3px;"><strong>Fantasia:</strong> ${visit.client_social_name || visit.client_name}</p>
                                <p style="font-size: 0.85em; margin-bottom: 3px;"><strong>Agendado para:</strong> ${visit.scheduled_at_formatted}</p>
                                <p style="font-size: 0.85em; margin-bottom: 3px;"><strong>Endereço:</strong> ${visit.address || 'N/A'}</p>
                                <p style="font-size: 0.85em; margin-bottom: 3px;"><strong>Status:</strong> ${visit.status}</p>
                                <a href="${visit.edit_url}" style="font-size: 0.85em; color: #3b82f6; text-decoration: underline;">Ver Detalhes da Visita</a>
                            </div>
                        `;
                            infoWindow.setContent(content);
                            infoWindow.open(map, marker);
                        });

                        bounds.extend(markerPosition);
                    });

                    if (visitsData.length > 0) {
                        map.fitBounds(bounds);
                        if (visitsData.length === 1) {
                            map.setZoom(15);
                        }
                    }
                }
            </script>
            <script
                src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&loading=async&libraries=marker&mapId={{ env('GOOGLE_MAPS_API_MAP_ID') }}&callback=initScheduledVisitsMap"></script>
        @endpush
    @endif
</div>
