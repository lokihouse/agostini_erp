<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{
        state: $wire.$entangle('{{ $getStatePath() }}'),
        async init() {
            await google.maps.importLibrary('maps')
            var chicago = new google.maps.LatLng(0, 0);
            var mapOptions = {
                zoom: 1,
                center: chicago,
                mapId: @js(env('GOOGLE_MAPS_API_MAP_ID')),
                disableDefaultUI: true,
            }
            var map = new google.maps.Map(document.getElementById('map'), mapOptions);
            console.log('OPA', document.getElementById('map') );
        }
    }">
        <div id="map" class="w-full h-96"></div>
    </div>
</x-dynamic-component>
