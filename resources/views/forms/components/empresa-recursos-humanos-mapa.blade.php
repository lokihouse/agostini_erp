@php
    use Illuminate\Support\Str;
    $mapId = 'map-' . Str::random(8);
@endphp
<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @assets
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    @endassets

    <div x-data="{
        state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }}.live,
        getState() { return this.state },
        init() {
            setTimeout(() => {
                var center = [$data.getState().latitude ?? 0, $data.getState().longitude ?? 0];
                var map = L.map('{{$mapId}}').setView(center, 15);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: ''
                }).addTo(map);
                L.marker(center).addTo(map);
                L.circle(center, {
                    color: '#ff0033',
                    fillColor: '#ff003333',
                    fillOpacity: 0.5,
                    radius: $data.getState().raio
                }).addTo(map);
            }, 1)
        }
    }">
        <div id="{{$mapId}}" style="width: 100%; height: 300px;"></div>
    </div>
</x-dynamic-component>
