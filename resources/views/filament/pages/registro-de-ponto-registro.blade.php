<x-filament-panels::page>
    @script
        <script>
            document.addEventListener('livewire:initialized', () => {
                let map = null;
                let markerPin = null;
                let markerCircle = null;
                let latitude = null;
                let longitude = null;
                let accuracy = null;

                function getCardinalPoints(latitude, longitude, radius, directions = ['NE', 'SE', 'SW', 'NW']) {
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
                        SE: 135,
                        SW: 225,
                        NW: 315
                    };

                    return [
                        directions.indexOf('NE') > -1 ? calculatePoint(latitude, longitude, toRadians(bearings.NE), radiusInKm) : null,
                        directions.indexOf('SE') > -1 ? calculatePoint(latitude, longitude, toRadians(bearings.SE), radiusInKm) : null,
                        directions.indexOf('SW') > -1 ? calculatePoint(latitude, longitude, toRadians(bearings.SW), radiusInKm) : null,
                        directions.indexOf('NW') > -1 ? calculatePoint(latitude, longitude, toRadians(bearings.NW), radiusInKm) : null
                    ].filter(n => n);
                }

                function makeMarkersOnMap() {

                    if(markerPin !== null && (markerPin._latlng.lat !== latitude || markerPin._latlng.lng !== longitude )) {
                        markerPin.remove();
                        markerPin = null;
                    }
                    if(markerPin === null) {
                        markerPin = L.marker([latitude, longitude]).addTo(map);
                    }

                    if(markerCircle !== null && (markerCircle._latlng.lat !== latitude || markerCircle._latlng.lng !== longitude )) {
                        markerCircle.remove();
                        markerCircle = null
                    }
                    if(markerCircle === null){
                        markerCircle = L.circle([latitude, longitude], {
                            color: 'red',
                            fillColor: '#f03',
                            fillOpacity: 0.20,
                            radius: accuracy
                        }).addTo(map);
                    }
                    map.fitBounds(getCardinalPoints(latitude, longitude, accuracy + 500, ['SW', 'NE']));
                }

                function callMap() {
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
                }

                function setData() {
                    const livewireComponentEl = document.querySelector('.registro-de-ponto-page-endereco');
                    const livewireComponent = Livewire.find(livewireComponentEl.getAttribute('wire:id'));
                    livewireComponent.set('latitude', latitude);
                    livewireComponent.set('longitude', longitude);
                    livewireComponent.set('accuracy', accuracy);
                    livewireComponent.set('tipo', $wire.get('tipo'));
                }

                function getGeoLocaion() {
                    if (!navigator.geolocation) {
                        alert('O seu navegador não suporta a geolocalização. Entre em contato com o suporte.')
                    } else {
                        new Promise((resolve) => {
                            navigator.geolocation.getCurrentPosition(
                                (obj) => {
                                    latitude = obj.coords.latitude
                                    longitude = obj.coords.longitude
                                    accuracy = obj.coords.accuracy
                                    makeMarkersOnMap()
                                    setData()
                                },
                                (err) => {
                                    alert('O seu navegador não suporta a geolocalização. Entre em contato com o suporte.')
                                },
                                {
                                    enableHighAccuracy: true,
                                    timeout: 5000,
                                    maximumAge: 0,
                                }
                            )
                        })
                    }
                }

                callMap(0, 0)

                setTimeout(() => {
                    getGeoLocaion();
                    setInterval(() => {
                        getGeoLocaion()
                    }, 1*1000)
                }, 100);
            })
        </script>
    @endscript

    <div class="flex flex-grow absolute top-0 left-0 z-0">
        <div id="map" class="relative w-screen h-screen z-10"></div>

        <livewire:registro-de-ponto-page-relogio tipo="{{$this->tipo}}"/>

        <livewire:registro-de-ponto-page-endereco/>
    </div>
</x-filament-panels::page>
