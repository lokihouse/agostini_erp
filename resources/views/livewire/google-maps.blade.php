@script
<script>
    let map;

    async function initMap() {
        const { Map } = await google.maps.importLibrary("maps");
        const { AdvancedMarkerElement, PinElement } = await google.maps.importLibrary("marker");

        const dados = []; //;

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };
                    map.setCenter(pos);
                }
            );
        }

        map = new Map(document.getElementById("map"), {
            center: { lat: -15.7801, lng: -47.9292 },
            zoom: 8,
            mapId: @js(env('GOOGLE_MAPS_API_MAP_ID')),
            disableDefaultUI: true,
        });

        dados.forEach((dt) => {
            (new AdvancedMarkerElement({
                map,
                position: { lat: dt.coord[0], lng: dt.coord[1] },
                title: dt.label,
                content: (new PinElement({
                    borderColor: '#000',
                    glyphColor: dt.pínBgColor,
                    background: dt.pínBgColor,
                })).element,
            })).addListener("click", () => {
                window.location.href = dt.rota;
            });
        })
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
</script>
@endscript

<div>
    <div id="map" class="w-full h-96 z-0 bg-red-500"></div>
</div>
