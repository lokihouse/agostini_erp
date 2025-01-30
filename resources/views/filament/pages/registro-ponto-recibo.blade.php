<x-filament-panels::page>
    @script
    <script>
        document.addEventListener('livewire:initialized', () => {
            let record = @json($this->record) ?? {}
            let map = null;
            let latitude = record.latitude ?? null;
            let longitude = record.longitude ?? null;
            let accuracy = record.accuracy ?? null;

            let marker = [latitude ?? 0, longitude ?? 0]
            let tileLayer = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {attribution: false});

            map = L.map('map',
                {
                    zoomControl: true,
                    layers: [tileLayer],
                    maxZoom: 18,
                    minZoom: 6
                }).setView(marker, 17);

            map.zoomControl.remove()
            map.dragging.disable()
            map.scrollWheelZoom.disable()

            setTimeout(function () {
                map.invalidateSize(true)
            }, 100);

            L.marker([latitude, longitude]).addTo(map);
            L.circle([latitude, longitude], {
                color: 'red',
                fillColor: '#f03',
                fillOpacity: 0.20,
                radius: accuracy
            }).addTo(map);
            map.fitBounds(getCardinalPoints(accuracy + 500)).panBy([0, -250], {animate: true, duration: 1});

            function getCardinalPoints(radius) {
                const EARTH_RADIUS = 6371; // Raio da Terra em km
                const radiusInKm = radius / 1000; // Converter o raio para km

                // Converter graus para radianos
                const toRadians = (degrees) => degrees * (Math.PI / 180);
                // Converter radianos para graus
                const toDegrees = (radians) => radians * (180 / Math.PI);

                // Função para calcular deslocamentos
                const calculatePoint = (lat, lng, bearing, distance) => {
                    const latRad = toRadians(lat);
                    const lngRad = toRadians(lng);

                    const newLat = Math.asin(
                        Math.sin(latRad) * Math.cos(distance / EARTH_RADIUS) +
                        Math.cos(latRad) * Math.sin(distance / EARTH_RADIUS) * Math.cos(bearing)
                    );

                    const newLng = lngRad + Math.atan2(
                        Math.sin(bearing) * Math.sin(distance / EARTH_RADIUS) * Math.cos(latRad),
                        Math.cos(distance / EARTH_RADIUS) - Math.sin(latRad) * Math.sin(newLat)
                    );

                    return [
                        toDegrees(newLat),
                        toDegrees(newLng)
                    ];
                };

                const bearings = {
                    NE: 45,
                    SW: 225,
                };

                return [
                    calculatePoint(latitude, longitude, toRadians(bearings.NE), radiusInKm),
                    calculatePoint(latitude, longitude, toRadians(bearings.SW), radiusInKm),
                ];
            }

        })
    </script>
    @endscript

    <div class="flex flex-grow absolute top-0 left-0 z-0">
        <div id="map" class="relative w-screen h-screen z-10"></div>

        <div class="absolute top-14 w-full z-50 flex justify-center">
            <div class="bg-white rounded-xl border shadow-lg m-4 p-4 max-w-md w-full">
                <div class="text-center text-xl font-bold">Registro #{{ $this->record->id }}</div>
                <div class="text-center text-xl font-thin">Ponto registrado com sucesso</div>
                <div class="text-center text-xl font-bold pt-4">{{ $this->record->usuario->nome }}</div>
                <div id="timer" class="text-center text-3xl font-bold pt-4">{{ \Carbon\Carbon::parse($this->record->data)->translatedFormat('H:i:s') }}</div>
                <div id="calendar" class="text-center text-md font-thin">{{ \Carbon\Carbon::parse($this->record->data)->translatedFormat('d \de F \de Y') }}</div>
                <div id="calendar" class="text-center text-md font-thin pt-4">{{ $this->record->address }}</div>
            </div>
        </div>

        <div class="absolute bottom-0 w-full z-50 flex justify-center">
            <div class="bg-white rounded-xl border shadow-lg m-4 p-4 max-w-md w-full">
                @php
                $hashGroupSize = 4;
                $hashChunkSize = 8;

                $hashChunk = explode(".", chunk_split($this->record->hash, 8, '.'));
                if($hashChunk[count($hashChunk) -1]=="") unset($hashChunk[count($hashChunk) -1]);

                $pos = 0;
                @endphp
                <div id="calendar" class="text-center text-xs font-thin">
                    Código validador:<br/>
                    @while($pos < count($hashChunk))
                        @for($i = 0; $i < $hashGroupSize; $i++)
                            @if($pos < count($hashChunk))
                                {{$hashChunk[$pos]}}.
                            @endif
                            @php($pos++)
                        @endfor
                        <br/>
                    @endwhile
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
