@php

use App\Models\Visita;
    use Carbon\Carbon;use Illuminate\Support\Facades\DB;

if(auth()->user()->can('widget_MapaDeVisitas')){
    $dados = [];

    $clientes = \App\Models\Cliente::query()->where('user_id', auth()->user()->id)->get()->toArray();

    foreach ($clientes as $cliente){
        $ultimaVisita = Visita::query()
            ->where('cliente_id', $cliente['id'])
            ->where('data', '<', Carbon::parse('today')->format('Y-m-d'))
            ->whereIn('status', ['agendada'])
            ->orderBy('data', 'desc')
            ->first();
        $visitaEmAndamento = Visita::query()
            ->where('cliente_id', $cliente['id'])
            ->whereIn('status', ['em andamento'])
            ->orderBy('data', 'desc')
            ->first();
        $proximaVisita = Visita::query()
            ->where('cliente_id', $cliente['id'])
            ->where('data', '>=', Carbon::parse('today')->format('Y-m-d'))
            ->whereIn('status', ['agendada'])
            ->orderBy('data', 'asc')
            ->first();

        if (!is_null($ultimaVisita) && !is_null($visitaEmAndamento)) {
            $visita = $visitaEmAndamento;
        } elseif (!is_null($ultimaVisita) && is_null($visitaEmAndamento)) {
            $visita = $ultimaVisita;
        } elseif (is_null($ultimaVisita) && !is_null($visitaEmAndamento)) {
            $visita = $visitaEmAndamento;
        } elseif (is_null($ultimaVisita) && is_null($visitaEmAndamento) && !is_null($proximaVisita)) {
            $visita = $proximaVisita;
        } else {
            continue;
        }

        $dados[] = [
            "label" => $cliente['nome_fantasia'],
            "coord" => [$cliente['latitude'], $cliente['longitude']],
            "rota" => route('filament.app.pages.registro-de-visita', $visita['id']),
            "data" => $visita['data'],
            "status" => $visita['status'],
            "pínBgColor" => translateStatusForColors($visita['status']),
            "ultimaVisita" => $ultimaVisita ? $ultimaVisita->toArray() : null,
            "proximaVisita" => $proximaVisita ? $proximaVisita->toArray() : null
        ];
    }
}
    // dd($dados);

function translateStatusForColors($status): string {
    switch($status){
        default:
            return '';
        case 'agendada':
            return '#e7e5e4';
        case 'em andamento':
            return '#eab308';
        case 'realizada':
            return '#047857';
        case 'cancelada':
            return '#b91c1c';
    }
}

@endphp
<div>
    @if(auth()->user()->can('widget_MapaDeVisitas'))
    <x-filament::section>
        @script
        <script>
            let map;

            async function initMap() {
                const { Map } = await google.maps.importLibrary("maps");
                const { AdvancedMarkerElement, PinElement } = await google.maps.importLibrary("marker");

                const dados = @json($dados);

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
                    mapId: '68c5e63e97cfdbec',
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

            document.addEventListener('livewire:initialized', () => {
                initMap();
            })
        </script>
        @endscript

        <div class="flex flex-col">

            {{ $this->agendarVisitaAction() }}

            <div class="text-[8px] grid grid-cols-4 gap-1 py-2">
                <div class="py-1 rounded text-center bg-[{{ translateStatusForColors("agendada") }}]">
                    Agendada
                </div>
                <div class="py-1 rounded text-center bg-[{{ translateStatusForColors("em andamento") }}]">
                    Em Andamento
                </div>
                <div class="py-1 rounded text-center bg-[{{ translateStatusForColors("realizada") }}] text-white">
                    Realizada
                </div>
                <div class="py-1 rounded text-center bg-[{{ translateStatusForColors("cancelada") }}] text-white">
                    Cancelada
                </div>
            </div>

            <div id="map" class="w-full h-96 z-0"></div>

            <div class="">
                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="w-1">
                                &nbsp;
                            </th>
                            <th scope="col" class="p-3">
                                Cliente
                            </th>
                            <th scope="col" class="w-1 p-3 text-center">
                                Data
                            </th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($dados as $dado)

                            <tr wire:click="goToPage(@js($dado["rota"]))"
                                class="cursor-pointer bg-white text-xs border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                                <td class="ps-3">
                                    <span
                                        class="{{ 'flex w-3 h-3 bg-[' . translateStatusForColors($dado['status']) . '] rounded-full' }}"></span>
                                </td>
                                <td class="p-3">
                                    {{ $dado['label'] }}
                                </td>
                                <td class="p-3 text-center">
                                    {{ \Carbon\Carbon::parse($dado['data'])->format('d/m/y') }}
                                </td>
                            </tr>

                        @endforeach
                        </tbody>
                    </table>
                </div>

                <x-filament-actions::modals id="modalVisita"/>

            </div>
        </div>
    </x-filament::section>
    @endif
</div>
