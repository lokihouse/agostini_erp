<x-filament-panels::page>
    @script
    <script>
        document.addEventListener('livewire:initialized', async () => {
            const { Map } = await google.maps.importLibrary("maps");
            const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

            let map;
            let markerPin;
            let markerCircle;

            async function initMap() {
                let pos = {lat: 0, lng: 0}
                map = new google.maps.Map(document.getElementById("map"), {
                    center: pos,
                    zoom: 1,
                    mapId: @js(env('GOOGLE_MAPS_API_MAP_ID')),
                    disableDefaultUI: true,
                });

                markerPin = new AdvancedMarkerElement({
                    map,
                    position: pos,
                });

                markerCircle = new google.maps.Circle({
                    strokeColor: "#FF0000",
                    strokeOpacity: 0.3,
                    strokeWeight: 1,
                    fillColor: "#FF0000",
                    fillOpacity: 0.05,
                    map,
                    center: pos,
                    radius: 1000,
                });
            }

            function setData(latitude, longitude, accuracy) {
                const livewireComponentEl = document.querySelector('.registro-de-ponto-registro-endereco');
                const livewireComponent = Livewire.find(livewireComponentEl.getAttribute('wire:id'));
                livewireComponent.set('latitude', latitude);
                livewireComponent.set('longitude', longitude);
                livewireComponent.set('accuracy', accuracy);
            }

            async function callGeolocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const pos = {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude,
                                alt: 0
                            };

                            if( map.getCenter().lat() === pos.lat && map.getCenter().lng() === pos.lng){
                                return;
                            }

                            setData(pos.lat, pos.lng, position.coords.accuracy);

                            map.setCenter(pos);
                            markerPin.position = pos
                            markerCircle.setCenter(pos)
                            markerCircle.setRadius(position.coords.accuracy)
                            map.fitBounds(markerCircle.getBounds());
                        }
                    );
                } else {
                    handleLocationError(false, infoWindow, map.getCenter());
                }
            }

            function handleLocationError(browserHasGeolocation, infoWindow, pos) {
                infoWindow.setPosition(pos);
                infoWindow.setContent(
                    browserHasGeolocation
                        ? "Error: The Geolocation service failed."
                        : "Error: Your browser doesn't support geolocation.",
                );
                infoWindow.open(map);
            }

            initMap();

            setInterval(callGeolocation, 1000)
        })
    </script>
    @endscript

    <div class="flex flex-grow absolute top-0 left-0 z-0">
        <div id="map" class="relative w-screen h-screen z-10"></div>

        <livewire:registro-de-ponto-registro-relogio />

        <livewire:registro-de-ponto-registro-endereco/>
    </div>
</x-filament-panels::page>
