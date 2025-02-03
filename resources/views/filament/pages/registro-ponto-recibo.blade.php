<x-filament-panels::page>
    @script
    <script>
        document.addEventListener('livewire:initialized', async () => {
            const { Map } = await google.maps.importLibrary("maps");
            const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

            let record = @json($this->record) ?? {}
            let map;
            let markerCircle;
            let latitude = record.latitude ?? null;
            let longitude = record.longitude ?? null;
            let accuracy = record.accuracy ?? null;

            let pos = {lat: latitude, lng: longitude}
            map = new google.maps.Map(document.getElementById("map"), {
                center: pos,
                zoom: 1,
                mapId: @js(env('GOOGLE_MAPS_API_MAP_ID')),
                disableDefaultUI: true,
            });

            new AdvancedMarkerElement({
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
                radius: accuracy,
            });

            map.fitBounds(markerCircle.getBounds());

        })
    </script>
    @endscript

    <div class="flex flex-grow absolute top-0 left-0 z-0">
        <div id="map" class="relative w-screen h-screen z-10"></div>

        <div class="absolute top-14 w-full z-50 flex justify-center">
            <div class="bg-white rounded-xl border shadow-lg m-4 p-4 max-w-md w-full">
                <div class="text-center text-xl font-thin">Ponto registrado com sucesso</div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="flex justify-center items-center text-center text-xl font-bold pt-4">
                        {{ $this->record->usuario->nome }}
                    </div>
                    <div>
                        <div id="timer" class="text-center text-3xl font-bold pt-4">{{ \Carbon\Carbon::parse($this->record->data)->translatedFormat('H:i:s') }}</div>
                        <div id="calendar" class="text-center text-md font-thin">{{ \Carbon\Carbon::parse($this->record->data)->translatedFormat('d \de F \de Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="absolute bottom-0 w-full z-50 flex justify-center">
            <div class="bg-white rounded-xl border shadow-lg m-4 p-4 max-w-md w-full">
                @php
                $hashGroupSize = 3;
                $hashChunkSize = 8;

                $hashChunk = explode(".", chunk_split($this->record->hash, 8, '.'));
                if($hashChunk[count($hashChunk) -1]=="") unset($hashChunk[count($hashChunk) -1]);

                $pos = 0;
                @endphp
                <div class="text-xs grid grid-cols-2 gap-2">
                    <div class="col-span-full">
                        <span class="font-bold">Você esta aqui:</span>&nbsp;
                        <span id="address" class="font-thin">{{ $this->record->address }}</span>
                    </div>
                    <div class="space-y-2">
                        <div>
                            <div class="font-bold">Coordenadas:</div>
                            <div class="font-thin">
                                {{ \Illuminate\Support\Number::format($this->record->latitude,6) }}, {{ \Illuminate\Support\Number::format($this->record->longitude,6) }}
                            </div>
                        </div>
                        <div>
                            <div class="font-bold">Precisão:</div>
                            <div class="font-thin">
                                {{ \Illuminate\Support\Number::format($this->record->accuracy,0) }} m
                            </div>
                        </div>
                    </div>
                    <div id="calendar" class="text-center text-xs font-thin">
                        <span class="font-bold">Código validador:</span><br/>
                        @while($pos < count($hashChunk))
                            @for($i = 0; $i < $hashGroupSize; $i++)
                                @if($pos < count($hashChunk) - 1)
                                    {{$hashChunk[$pos]}}.
                                @elseif($pos < count($hashChunk))
                                    {{$hashChunk[$pos]}}
                                @endif
                                @php($pos++)
                            @endfor
                            <br/>
                        @endwhile
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
