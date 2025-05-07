{{-- resources/views/livewire/time-clock/map-register-point.blade.php --}}
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- Importante para POST requests --}}
    <title>Registrar Ponto - Mapa</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        #map { height: calc(100vh - 120px); width: 100%; } /* Ajustar altura para caber botões */
        body, html { margin: 0; padding: 0; overflow: hidden; }
        .controls { padding: 15px; text-align: center; background-color: #f8f9fa; border-top: 1px solid #dee2e6; }
        .controls button {
            padding: 10px 20px;
            font-size: 16px;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-confirm { background-color: #28a745; } /* Verde */
        .btn-confirm:hover { background-color: #218838; }
        .btn-cancel { background-color: #dc3545; margin-left: 10px; } /* Vermelho */
        .btn-cancel:hover { background-color: #c82333; }
        #loading-message, #error-message { text-align: center; padding-top: 20px; }
        #error-message { color: red; }
    </style>
</head>
<body>
<div id="map-container">
    <div id="loading-message">Carregando mapa e localização... (Certifique-se de permitir o acesso à localização)</div>
    <div id="error-message" style="display:none;"></div>
    <div id="map" style="display:none;"></div>
</div>

<div class="controls">
    <form id="timeClockForm" method="POST" action="{{ route('time-clock.store') }}" style="display: none;">
        @csrf
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">
        <input type="hidden" name="actionType" value="{{ $actionType }}"> {{-- actionType vindo da rota --}}

        <button type="submit" class="btn-confirm">Confirmar Batida ({{ \App\Models\TimeClockEntry::getEntryTypeOptions()[$actionType] ?? $actionType }})</button>
        <button type="button" class="btn-cancel" onclick="window.location.href='{{ route('filament.app.pages.home-page') }}'">Cancelar</button>
    </form>
</div>

{{-- Inclua a API do Google Maps. Substitua YOUR_GOOGLE_MAPS_API_KEY pela sua chave --}}
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCjtviMDS4x0iIx1IqrsLlV3jHOXUEwbgg&callback=initMap"></script>
<script>
    let userMarker;
    let mapInstance;

    function initMap() {
        const loadingMessage = document.getElementById('loading-message');
        const errorMessage = document.getElementById('error-message');
        const mapDiv = document.getElementById('map');
        const form = document.getElementById('timeClockForm');

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                loadingMessage.style.display = 'none';
                mapDiv.style.display = 'block';
                form.style.display = 'block';

                const userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };

                document.getElementById('latitude').value = userLocation.lat;
                document.getElementById('longitude').value = userLocation.lng;

                mapInstance = new google.maps.Map(mapDiv, {
                    center: userLocation,
                    zoom: 17, // Zoom maior para mais precisão
                    mapTypeControl: false, // Opcional: remove controle de tipo de mapa
                    streetViewControl: false // Opcional: remove street view
                });

                userMarker = new google.maps.Marker({
                    position: userLocation,
                    map: mapInstance,
                    title: 'Sua Localização Atual'
                });

            }, function(error) {
                loadingMessage.style.display = 'none';
                errorMessage.style.display = 'block';
                let msg = 'Erro: ';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        msg += "Usuário negou a solicitação de Geolocalização.";
                        break;
                    case error.POSITION_UNAVAILABLE:
                        msg += "Informações de localização indisponíveis.";
                        break;
                    case error.TIMEOUT:
                        msg += "A solicitação para obter a localização do usuário expirou.";
                        break;
                    case error.UNKNOWN_ERROR:
                        msg += "Ocorreu um erro desconhecido.";
                        break;
                }
                errorMessage.textContent = msg;
                console.error(msg, error);
            }, {
                enableHighAccuracy: true, // Tenta obter a localização mais precisa
                timeout: 10000, // Tempo limite de 10 segundos
                maximumAge: 0 // Não usa cache de localização
            });
        } else {
            loadingMessage.style.display = 'none';
            errorMessage.style.display = 'block';
            errorMessage.textContent = 'Erro: Seu navegador não suporta geolocalização.';
        }
    }

    // Se você tiver mensagens de erro do backend (validação, etc.)
    @if (session('error_message') || (isset($errors) && $errors->any()))
        window.onload = () => {
        const loadingMessage = document.getElementById('loading-message');
        const errorMessage = document.getElementById('error-message');
        loadingMessage.style.display = 'none';
        errorMessage.style.display = 'block';
        errorMessage.innerHTML = `
                    <p>Ocorreram erros ao tentar registrar o ponto:</p>
                    <ul>
                        @if(session('error_message'))<li>{{ session('error_message') }}</li>@endif
        @if(isset($errors) && $errors->any()) {{-- Check if $errors is set and has messages --}}
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
            @endforeach
        @endif
        </ul>
        <p>Por favor, tente novamente ou contate o suporte.</p>
`;
        // Poderia também tentar re-inicializar o mapa se os erros não forem de GPS
        // initMap(); // Cuidado com loops se o erro for persistente
    };
    @endif
</script>
</body>
</html>
