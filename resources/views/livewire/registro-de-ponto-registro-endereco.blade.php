@script
<script>
    Livewire.hook('morph.updated', (el, component) => {
        if(el.el.classList.contains('registro-de-ponto-registro-endereco')){
            if($wire.get('latitude') !== $wire.get('old_latitude') || $wire.get('longitude') !== $wire.get('old_longitude')) {
                $wire.set('old_latitude', $wire.get('latitude'));
                $wire.set('old_longitude', $wire.get('longitude'));

                let latitude = $wire.get('latitude') ?? null;
                let longitude = $wire.get('longitude') ?? null;

                if (latitude === null || longitude === null) return;

                const apiKey = "{{ env('GOOGLE_MAPS_API_KEY') }}";
                const url = `https://maps.googleapis.com/maps/api/geocode/json?latlng=${latitude},${longitude}&key=${apiKey}`;

                console.log(url);

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === "OK") {
                            $wire.set('googleRequestStatus', 'OK');
                            const address = data.results[0]?.formatted_address;
                            console.log("Endereço:", address);
                            $wire.set("address", address);
                        } else {
                            console.error("Erro:", data.status);
                        }
                    })
                    .catch(error => console.error("Erro ao buscar endereço:", error));
            }
        }
    });
</script>
@endscript
<div class="registro-de-ponto-registro-endereco absolute bottom-0 w-full z-50 flex justify-center dark:text-gray-700">
    <div class="bg-white rounded-xl border shadow-lg m-4 p-4 max-w-md w-full">
        @if($this->googleRequestStatus === null)
            <div class="flex items-center justify-center">
                <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-blue-500 border-solid border-gray-200"></div>
            </div>
        @endif

        @if($this->googleRequestStatus === 'OK')
            <div>
                <span class="font-bold">Você esta aqui:</span>&nbsp;
                <span id="address" class="font-thin">{{ $this->address }}</span>
            </div>
            <div class="text-xs grid grid-cols-2 gap-2">
                <div>
                    <div class="font-bold">Coordenadas:</div>
                    <div class="font-thin">
                        {{ \Illuminate\Support\Number::format($this->latitude,6) }}, {{ \Illuminate\Support\Number::format($this->longitude,6) }}
                    </div>
                </div>
                <div>
                    <div class="font-bold">Precisão:</div>
                    <div class="font-thin">
                        {{ \Illuminate\Support\Number::format($this->accuracy,0) }} m
                    </div>
                </div>
            </div>
            <x-filament::button class="w-full mt-4"  wire:click="registrarPonto" >
                <div class="flex  items-center p-2 space-x-2">
                    <div class="font-bold">Registrar Ponto</div>
                </div>
            </x-filament::button>
        @endif

        @if($this->googleRequestStatus === 'ERROR')
            <div class="text-center text-red-500">Falha ao recuperar a sua localização. Por favor, recarregue a páginá e tente novamente.</div>
        @endif
    </div>
</div>
