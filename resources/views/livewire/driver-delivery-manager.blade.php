<div class="p-3 space-y-3 bg-white rounded-lg shadow"> <!-- Reduzido p-4 space-y-4 para p-3 space-y-3 -->
    <div class="grid flex-1 gap-y-0.5"> <!-- Reduzido gap-y-1 para gap-y-0.5 -->
        <h3 class="font-semibold leading-6 fi-section-header-heading text-md text-gray-950 dark:text-white"> <!-- text-base para text-md -->
            Minhas Entregas
        </h3>
        <p class="text-xs text-gray-500 fi-section-header-description dark:text-gray-400">
            Visualização das próximas entregas.
        </p>
    </div>

    <!-- Ordem em Progresso -->
    @if ($inProgressOrder)
        <div class="p-2 border border-blue-300 rounded-lg bg-blue-50"> <!-- Reduzido p-3 para p-2 -->
            <h3 class="text-sm font-medium text-blue-700">Ordem em Progresso: {{ $inProgressOrder->transport_order_number }}</h3> <!-- text-md para text-sm -->

            @if ($nextDeliveryItem)
                <div class="p-2 mt-2 border border-green-300 rounded bg-green-50"> <!-- Reduzido mt-3 p-3 para mt-2 p-2 -->
                    <h4 class="text-xs font-semibold text-green-700">Próxima Entrega (Sequência: {{ $nextDeliveryItem->delivery_sequence }})</h4> <!-- text-sm para text-xs -->

                    <div class="mt-1 space-y-0.5"> <!-- Reduzido mt-2 space-y-1 para mt-1 space-y-0.5 -->
                        <p class="text-xs"><strong>Cliente:</strong> {{ $nextDeliveryItem->client->name }}</p> <!-- text-sm para text-xs -->
                        <div>
                            <p class="text-xs font-medium text-gray-700"><strong>Endereço de Destino:</strong></p>
                            <p class="text-sm font-semibold text-gray-800">{{ $nextDeliveryItem->delivery_address_snapshot }}</p> <!-- text-md para text-sm -->
                        </div>
                    </div>

                    <div class="mt-2 space-y-1.5">
                        @php
                            $mapsUrl = '';
                            if ($nextDeliveryItem->client->latitude && $nextDeliveryItem->client->longitude) {
                                $mapsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' . $nextDeliveryItem->client->latitude . ',' . $nextDeliveryItem->client->longitude;
                            } elseif ($nextDeliveryItem->delivery_address_snapshot) {
                                $mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($nextDeliveryItem->delivery_address_snapshot);
                            }
                        @endphp

                        @if ($mapsUrl)
                            <x-filament::button
                                tag="a"
                                href="{{ $mapsUrl }}"
                                target="_blank"
                                icon="heroicon-m-map-pin"
                                color="primary"
                                size="xs"
                                class="w-full"
                            >
                                Navegar para o Cliente
                            </x-filament::button>
                        @endif

                        <x-filament::button
                            wire:click="openQrScanModal('{{ $nextDeliveryItem->uuid }}')"
                            size="xs"
                            icon="heroicon-m-qr-code"
                            color="success"
                            class="w-full"
                        >
                            Realizar entrega
                        </x-filament::button>
                    </div>
                </div>
            @else
                <p class="mt-2 text-xs text-gray-600">Todos os itens desta ordem foram processados!</p> <!-- text-sm para text-xs -->
            @endif

            <div class="mt-2"> <!-- Reduzido mt-3 para mt-2 -->
                <x-filament::button color="gray" wire:click="openQrScanModal(null)" size="xs">
                    Escanear QR Code de Outro Item
                </x-filament::button>
            </div>
        </div>
    @else
        <!-- Ordens Aprovadas para Iniciar -->
        @if (count($approvedOrders) > 0)
            <div class="space-y-1.5"> <!-- Reduzido space-y-2 para space-y-1.5 -->
                <h3 class="text-xs font-medium text-gray-600">Ordens Prontas para Iniciar:</h3> <!-- text-sm para text-xs -->
                @foreach ($approvedOrders as $order)
                    <div class="p-1.5 border rounded-md flex justify-between items-center"> <!-- Reduzido p-2 para p-1.5 -->
                        <div>
                            <p class="text-xs"><strong>OT:</strong> {{ $order->transport_order_number }}</p> <!-- text-sm para text-xs -->
                            <p class="text-xs text-gray-500">Saída Prevista: {{ $order->planned_departure_datetime?->format('d/m/Y H:i') ?? 'N/A' }}</p>
                        </div>
                        <x-filament::button wire:click="startOrder('{{ $order->uuid }}')" size="xs">
                            Iniciar Ordem
                        </x-filament::button>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-xs text-gray-500">Nenhuma ordem de entrega pendente ou em progresso atribuída a você no momento.</p> <!-- text-sm para text-xs -->
        @endif
    @endif

    <!-- Modal de Scan de QR Code -->
    @if ($showQrScanModal)
        <!-- Modal de Scan de QR Code -->
        <div x-data="{
            // showQrScanModal: $wire.entangle('showQrScanModal'), // Se precisar de two-way binding com Livewire
            // A abordagem atual de controlar via $wire.showQrScanModal no x-show é mais comum para abrir/fechar
            scanErrorMessage: ''
         }"
             x-show="$wire.showQrScanModal"
             x-on:keydown.escape.window="if ($wire.showQrScanModal) { $wire.set('showQrScanModal', false); driverDeliveryStopScanner(); }"
             x-on:close-scanner-modal.window="if ($event.detail.target === 'driver-delivery') { $wire.set('showQrScanModal', false); driverDeliveryStopScanner(); scanErrorMessage = ''; }"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-800 bg-opacity-75"
             style="display: none;" {{-- Controlado pelo x-show --}}
             aria-labelledby="driver-scanner-modal-title" role="dialog" aria-modal="true"
             wire:ignore.self {{-- Importante para bibliotecas JS que manipulam o DOM --}}
        >
            <div class="relative w-full max-w-md p-4 bg-white rounded-lg shadow-xl dark:bg-gray-800"
                 x-on:click.outside="if ($wire.showQrScanModal) { $wire.set('showQrScanModal', false); driverDeliveryStopScanner(); }"
            >
                <div class="flex items-center justify-between mb-3">
                    <h3 id="driver-scanner-modal-title" class="font-medium text-gray-900 text-md dark:text-white">Escanear QR Code do Item</h3>
                    <button @click="$wire.set('showQrScanModal', false); driverDeliveryStopScanner();" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <p class="mb-3 text-xs text-center text-gray-600 dark:text-gray-400">Aponte a câmera para o QR code do item.</p>

                {{-- Área do Leitor --}}
                <div id="driver-qr-reader" style="width: 100%; max-width: 400px; margin: auto; border: 1px solid #ccc; min-height: 250px;">
                    {{-- O JS do html5-qrcode vai renderizar aqui --}}
                </div>

                {{-- Exibição de Erro do Scanner --}}
                <div class="mt-2 text-sm text-center text-red-600 dark:text-red-400" x-text="scanErrorMessage" x-show="scanErrorMessage"></div>

                {{-- Botão Fechar/Cancelar --}}
                <div class="mt-4 text-center">
                    <x-filament::button color="gray" size="xs" @click="$wire.set('showQrScanModal', false); driverDeliveryStopScanner();">
                        Cancelar
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Upload de Fotos -->
    @if ($showPhotoUploadModal && $currentItemForProcessing)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-800 bg-opacity-50" wire:ignore.self>
            <div class="w-full max-w-lg p-3 my-6 bg-white rounded-lg shadow-xl"> <!-- Reduzido p-4 para p-3, my-8 para my-6 -->
                <h3 class="text-sm font-medium mb-1.5">Registrar Fotos da Entrega</h3> <!-- text-md para text-sm, mb-2 para mb-1.5 -->
                <p class="text-xs text-gray-600 mb-0.5"><strong>Item:</strong> {{ $currentItemForProcessing->product->name }}
                    <br/>(Qtd: {{ number_format($currentItemForProcessing->quantity, 0, ',', '.') }})</p>
                <p class="mb-2 text-xs text-gray-600"><strong>Cliente:</strong> {{ $currentItemForProcessing->client->name }}</p> <!-- mb-3 para mb-2 -->

                <form wire:submit.prevent="savePhotosAndProceed">
                    <div>
                        <label for="photos" class="block text-xs font-medium text-gray-700">Selecionar Fotos (máx. 5)</label>
                        <x-filament::input.wrapper>
                            <x-filament::input type="file" wire:model="uploadedPhotos" id="photos" multiple accept="image/*" />
                        </x-filament::input.wrapper>
                        <div wire:loading wire:target="uploadedPhotos" class="mt-1 text-xs text-gray-500">Carregando fotos...</div>
                        @error('uploadedPhotos.*') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        @error('uploadedPhotos') <span class="text-xs text-red-500">{{ $message }}</span> @enderror

                        @if ($uploadedPhotos)
                            <div class="mt-2"> <!-- Reduzido mt-3 para mt-2 -->
                                <p class="text-xs font-medium">Pré-visualização:</p>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 mt-1"> <!-- gap-2 para gap-1.5 -->
                                    @foreach ($uploadedPhotos as $photo)
                                        @if(method_exists($photo, 'temporaryUrl'))
                                            <img src="{{ $photo->temporaryUrl() }}" class="object-cover w-full h-16 rounded"> <!-- h-20 para h-16 -->
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if ($currentItemForProcessing->delivery_photos)
                            <div class="mt-2"> <!-- Reduzido mt-3 para mt-2 -->
                                <p class="text-xs font-medium">Fotos Salvas Anteriormente:</p>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 mt-1"> <!-- gap-2 para gap-1.5 -->
                                    @foreach ($currentItemForProcessing->delivery_photos as $photoPath)
                                        <img src="{{ Storage::url($photoPath) }}" class="object-cover w-full h-16 rounded"> <!-- h-20 para h-16 -->
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="flex justify-end mt-3 space-x-2"> <!-- Reduzido mt-4 para mt-3 -->
                        <x-filament::button type="button" color="gray" wire:click="$set('showPhotoUploadModal', false)" size="xs"> <!-- size="sm" para size="xs" -->
                            Cancelar
                        </x-filament::button>
                        <x-filament::button type="submit" size="xs"> <!-- size="sm" para size="xs" -->
                            Salvar Fotos e Continuar
                        </x-filament::button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal de Confirmação de Entrega -->
    @if ($showConfirmationModal && $currentItemForProcessing)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50" wire:ignore.self>
            <div class="w-full max-w-md p-3 bg-white rounded-lg shadow-xl"> <!-- Reduzido p-4 para p-3 -->
                <h3 class="mb-2 text-sm font-medium">Confirmar Entrega</h3> <!-- text-md para text-sm, mb-3 para mb-2 -->
                <p class="mb-3 text-xs">A entrega do item <strong>{{ $currentItemForProcessing->product->name }}</strong> para o cliente <strong>{{ $currentItemForProcessing->client->name }}</strong> foi aceita?</p> <!-- mb-4 para mb-3, text-sm para text-xs -->
                <div class="flex justify-around">
                    <x-filament::button color="danger" wire:click="confirmDelivery(false)" size="xs"> <!-- size="sm" para size="xs" -->
                        Não (Rejeitada)
                    </x-filament::button>
                    <x-filament::button color="success" wire:click="confirmDelivery(true)" size="xs"> <!-- size="sm" para size="xs" -->
                        Sim (Aceita)
                    </x-filament::button>
                </div>
                <div class="mt-2 text-center"> <!-- Reduzido mt-3 para mt-2 -->
                    <x-filament::button type="button" color="gray" wire:click="$set('showConfirmationModal', false)" size="xs"> <!-- size="sm" para size="xs" -->
                        Cancelar
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Motivo da Rejeição -->
    @if ($showRejectionReasonModal && $currentItemForProcessing)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-50" wire:ignore.self>
            <div class="w-full max-w-md p-3 bg-white rounded-lg shadow-xl"> <!-- Reduzido p-4 para p-3 -->
                <h3 class="mb-2 text-sm font-medium">Motivo da Rejeição/Devolução</h3> <!-- text-md para text-sm, mb-3 para mb-2 -->
                <p class="text-xs text-gray-600 mb-0.5"><strong>Item:</strong> {{ $currentItemForProcessing->product->name }}</p> <!-- mb-1 para mb-0.5 -->
                <p class="mb-2 text-xs text-gray-600"><strong>Cliente:</strong> {{ $currentItemForProcessing->client->name }}</p> <!-- mb-3 para mb-2 -->
                <form wire:submit.prevent="submitRejection">
                    <div>
                        <label for="return_reason" class="block text-xs font-medium text-gray-700">Descreva o motivo:</label>
                        <x-filament::input.wrapper>
                            <textarea wire:model.defer="returnReason" id="return_reason" rows="3" class="block w-full text-xs transition duration-75 border-gray-300 rounded-lg shadow-sm fi-input focus:ring-1 focus:ring-inset disabled:opacity-70 dark:bg-white/5 dark:text-white focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:focus:border-primary-500 dark:focus:ring-primary-500"></textarea> <!-- text-sm para text-xs -->
                        </x-filament::input.wrapper>
                        @error('returnReason') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end mt-3 space-x-2"> <!-- Reduzido mt-4 para mt-3 -->
                        <x-filament::button type="button" color="gray" wire:click="$set('showRejectionReasonModal', false)" size="xs"> <!-- size="sm" para size="xs" -->
                            Cancelar
                        </x-filament::button>
                        <x-filament::button type="submit" size="xs"> <!-- size="sm" para size="xs" -->
                            Registrar Devolução
                        </x-filament::button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal de Confirmação de Mudança de Entrega -->
    @if ($showChangeDeliveryConfirmationModal && $itemActuallyScanned && $nextDeliveryItem)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-75" wire:ignore.self>
            <div class="w-full max-w-md p-3 bg-white rounded-lg shadow-xl">
                <h3 class="mb-2 text-sm font-medium">Confirmar Mudança de Entrega</h3>
                <p class="mb-1 text-xs">
                    O item escaneado: <strong>{{ $itemActuallyScanned->product->name }}</strong><br>
                    Para o cliente: <strong>{{ $itemActuallyScanned->client->name }}</strong>
                </p>
                <p class="mb-3 text-xs">
                    É diferente da próxima entrega programada: <strong>{{ $nextDeliveryItem->product->name }}</strong><br>
                    Para o cliente: <strong>{{ $nextDeliveryItem->client->name }}</strong>.
                </p>
                <p class="mb-3 text-xs font-semibold">Deseja processar o item escaneado agora?</p>

                <div class="flex justify-around">
                    <x-filament::button color="gray" wire:click="confirmChangeDeliveryAndProceed(false)" size="xs">
                        Não, Manter Próxima
                    </x-filament::button>
                    <x-filament::button color="warning" wire:click="confirmChangeDeliveryAndProceed(true)" size="xs">
                        Sim, Processar Escaneado
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
        <script>
            let driverDeliveryHtml5QrCode = null;
            let driverDeliveryDebounceTimer = null;
            const DRIVER_DELIVERY_DEBOUNCE_DELAY = 700;
            let driverDeliveryIsProcessingScan = false;

            function driverDeliveryOnScanSuccess(decodedText, decodedResult) {
                if (driverDeliveryIsProcessingScan) {
                    return;
                }
                console.log(`DriverDelivery: Code matched = ${decodedText}`, decodedResult);

                @this.set('scannedQrCodeData', decodedText, false);
                @this.call('processQrCodeScan');

                clearTimeout(driverDeliveryDebounceTimer);

                driverDeliveryDebounceTimer = setTimeout(() => {
                    driverDeliveryIsProcessingScan = true;
                    setTimeout(() => {
                        driverDeliveryIsProcessingScan = false;
                    }, 1000);
                }, DRIVER_DELIVERY_DEBOUNCE_DELAY);
            }

            function driverDeliveryOnScanFailure(error) {
                if (error && typeof error.includes === 'function' && !error.includes("NotFoundException")) {
                    console.warn(`DriverDelivery: Code scan error = ${error}`);
                    this.scanErrorMessage = 'Falha ao ler QR Code. Tente novamente.';
                }
            }

            function driverDeliveryStartScanner() {
                // console.log('DriverDelivery: Função startScanner chamada.');
                const qrReaderElement = document.getElementById('driver-qr-reader');
                if (!qrReaderElement) {
                    console.error("DriverDelivery: Elemento #driver-qr-reader não encontrado.");
                    return;
                }

                // Limpar scanner anterior se existir
                if (driverDeliveryHtml5QrCode) {
                    driverDeliveryStopScanner(); // Chama a função de parada para limpar corretamente
                }

                try {
                    driverDeliveryHtml5QrCode = new Html5Qrcode("driver-qr-reader");
                    const config = {
                        fps: 10,
                        qrbox: (viewfinderWidth, viewfinderHeight) => {
                            let minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                            let qrboxSize = Math.floor(minEdge * 0.75); // Um pouco maior
                            return { width: qrboxSize, height: qrboxSize };
                        },
                        rememberLastUsedCamera: true,
                        supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
                    };

                    driverDeliveryHtml5QrCode.start(
                        { facingMode: "environment" }, // Preferir câmera traseira
                        config,
                        driverDeliveryOnScanSuccess,
                        driverDeliveryOnScanFailure
                    ).catch((err) => {
                        console.error("DriverDelivery: Falha ao iniciar o scanner.", err);
                        // Atualiza a mensagem de erro no Alpine
                        // Acessar o escopo Alpine correto pode ser um pouco tricky daqui
                        // Uma forma é usar um evento global que o Alpine escuta
                        window.dispatchEvent(new CustomEvent('driver-delivery-scan-error', { detail: { message: 'Não foi possível acessar a câmera. Verifique as permissões.' }}));
                    @this.call('handleCameraError'); // Chama um método no Livewire para fechar o modal
                    });
                } catch (error) {
                    console.error("DriverDelivery: Erro ao criar ou configurar a instância Html5Qrcode.", error);
                    driverDeliveryHtml5QrCode = null; // Garante que está nulo se falhar
                }
            }

            function driverDeliveryStopScanner() {
                // console.log('DriverDelivery: Função stopScanner chamada.');
                clearTimeout(driverDeliveryDebounceTimer);
                driverDeliveryIsProcessingScan = false;

                if (driverDeliveryHtml5QrCode) {
                    const scannerInstanceToStop = driverDeliveryHtml5QrCode;
                    driverDeliveryHtml5QrCode = null; // Define como null ANTES de tentar parar

                    if (scannerInstanceToStop && typeof scannerInstanceToStop.getState === 'function' &&
                        scannerInstanceToStop.getState() === Html5QrcodeScannerState.SCANNING) {
                        // console.log("DriverDelivery: Tentando parar scanner ativo...");
                        scannerInstanceToStop.stop().then(() => {
                            // console.log("DriverDelivery: Scanner parado com sucesso.");
                            try { scannerInstanceToStop.clear(); } catch (e) { /* ignore */ }
                        }).catch((err) => {
                            console.error("DriverDelivery: Falha ao parar o scanner.", err);
                            try { scannerInstanceToStop.clear(); } catch (e) { /* ignore */ }
                        });
                    } else if (scannerInstanceToStop) {
                        // console.log("DriverDelivery: Scanner não estava ativo ou estado inválido. Tentando limpar.");
                        try { scannerInstanceToStop.clear(); } catch (e) { /* ignore */ }
                    }
                }
            }

            // Observar a propriedade Livewire para iniciar/parar o scanner
            // Isso é mais robusto do que depender apenas do x-init se o modal for re-renderizado
            document.addEventListener('livewire:initialized', () => {
                Livewire.hook('morph.updated', ({ el, component }) => {
                    // Verifique se o componente é o driver-delivery-manager e se o modal deve estar visível
                    if (component.name === 'driver-delivery-manager') {
                        // Acessar a propriedade showQrScanModal do componente Livewire
                        // Isso é um pouco mais complexo, pois o 'component' aqui é o objeto Livewire
                        // Vamos usar um evento disparado pelo Livewire quando o modal abre/fecha
                    }
                });
            });

            // Eventos para controlar o scanner a partir do Livewire
            window.addEventListener('openDriverDeliveryScanner', () => {
                // console.log('Evento openDriverDeliveryScanner recebido');
                // Garante que a variável Alpine scanErrorMessage seja limpa
                const alpineComponent = document.querySelector('[x-data*="scanErrorMessage"]');
                if (alpineComponent && alpineComponent.__x) {
                    alpineComponent.__x.refs ? alpineComponent.__x.refs.scanErrorMessage = '' : alpineComponent.__x.data.scanErrorMessage = '';
                }
                setTimeout(() => {
                    driverDeliveryStartScanner();
                }, 100);
            });

            window.addEventListener('closeDriverDeliveryScanner', () => {
                // console.log('Evento closeDriverDeliveryScanner recebido');
                driverDeliveryStopScanner();
            });

            // Limpar o scanner ao navegar para fora da página
            document.addEventListener('livewire:navigating', () => {
                // console.log('DriverDelivery: Livewire navigating detectado, chamando stopScanner.');
                driverDeliveryStopScanner();
            });

            // Para tratar a mensagem de erro da câmera no Alpine
            window.addEventListener('driver-delivery-scan-error', event => {
                const alpineComponent = document.querySelector('[x-data*="scanErrorMessage"]'); // Encontre o escopo Alpine do modal
                if (alpineComponent && alpineComponent.__x) {
                    alpineComponent.__x.data.scanErrorMessage = event.detail.message;
                }
            });

        </script>
    @endpush
</div>
