{{-- resources/views/livewire/user-task-control.blade.php --}}
<div x-data="{
        showScannerModal: false,
        showPauseModal: false, // Esta variável controlará o modal de pausa
        showFinishModal: false,
        scanErrorMessage: '', // Para exibir erros do scanner no modal
        isFinalizing: false // Controla se o scan é para finalizar tarefa
     }"
     x-on:scan-success.window="showScannerModal = false; stopScanner(); scanErrorMessage = ''; if (isFinalizing) { showFinishModal = true; isFinalizing = false; }" {{-- Fecha modal no sucesso e abre finish se estiver finalizando --}}
     x-on:scan-error.window="scanErrorMessage = $event.detail.message;" {{-- Mostra erro no modal --}}
     x-on:qr-code-scanned.window="if (!isFinalizing) { $wire.call('processScanResult', $event.detail.decodedText); } scanErrorMessage = '';" {{-- Chama Livewire no evento apenas se não estiver finalizando --}}
     x-on:close-pause-modal.window="showPauseModal = false; $wire.resetModalFields();" {{-- Adicionado para fechar via evento Livewire --}}
     x-on:close-finish-modal.window="showFinishModal = false; $wire.resetModalFields();" {{-- Adicionado para fechar modal de finalização via evento Livewire --}}
     class="bg-white shadow-sm fi-section rounded-xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
>
    {{-- Cabeçalho --}}
    <div class="px-6 py-4 border-b border-gray-200 fi-section-header-ctn dark:border-white/10">
        <div class="flex flex-col fi-section-header gap-y-2 sm:flex-row sm:items-center">
            <div class="grid flex-1 gap-y-1">
                <h3 class="text-base font-semibold leading-6 fi-section-header-heading text-gray-950 dark:text-white">
                    Minha Produção
                </h3>
                <p class="text-sm text-gray-500 fi-section-header-description dark:text-gray-400">
                    Gerencie sua atividade de produção atual.
                </p>
            </div>
        </div>
    </div>

    {{-- Conteúdo Principal --}}
    <div class="fi-section-content-ctn">
        <div class="p-2 fi-section-content">

            @if ($currentTask)
                {{-- ============================================= --}}
                {{--      EXIBIÇÃO QUANDO HÁ TAREFA ATIVA        --}}
                {{-- ============================================= --}}
                <div class="space-y-2">
                    {{-- Detalhes da Tarefa --}}
                    <div class="p-2 border border-gray-200 rounded-lg shadow-sm bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <div class="space-y-2 text-sm">
                                <div class="sm:col-span-1">
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">Ordem:</dt>
                                    <dd class="text-xs font-semibold text-gray-900 dark:text-white">{{ $orderNumber }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">Produto:</dt>
                                    <dd class="text-xs font-semibold text-gray-900 dark:text-white">{{ $productName }}</dd>
                                </div>
                                <div class="sm:col-span-3">
                                    <dt class="font-medium text-gray-500 dark:text-gray-400">Etapa:</dt>
                                    <dd class="text-xs font-semibold text-gray-900 dark:text-white">{{ $stepName }}</dd>
                                </div>
                            </div>
                            <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                <h4 class="mb-2 text-sm font-medium text-center text-gray-500 dark:text-gray-400">Progresso</h4>
                                <div class="flex items-baseline justify-center gap-x-2">
                                    <span class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ number_format($quantityProduced, 0, ',', '.') }}</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">/ {{ number_format($quantityPlanned, 0, ',', '.') }}</span>
                                </div>
                                <p class="mt-2 text-center text-sm font-semibold {{ $quantityRemaining > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-green-600 dark:text-green-400' }}">
                                    Restante: {{ number_format($quantityRemaining, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Progresso e Tempo --}}
                    <div class="">
                        <div class="flex flex-col items-center justify-center p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:col-span-2">
                            <h4 class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">Tempo na Tarefa</h4>
                            <div class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                                {{ $this->calculateTimeOnTask }}
                            </div>
                            @if($isPaused)
                                <span class="inline-flex items-center px-2 py-1 mt-1 text-xs font-medium text-yellow-800 rounded-md bg-yellow-50 ring-1 ring-inset ring-yellow-600/20 dark:bg-yellow-400/10 dark:text-yellow-500 dark:ring-yellow-400/20">
                                     <x-heroicon-s-pause class="-ml-0.5 mr-1.5 h-4 w-4"/>
                                     Pausado
                                     @if($currentTask && $currentTask->lastPauseReasonDetail)
                                        <span class="hidden ml-1 sm:inline"> - {{ $currentTask->lastPauseReasonDetail->name }}</span>
                                    @endif
                                 </span>
                                <x-filament::button 
                                    wire:click="resumeTask" 
                                    class="bg-green-600 hover:bg-green-700 text-black text-sm px-4 py-2 rounded-lg shadow-md transition-all"
                                    color="warning"
                                >
                                    Retomar Produção
                                </x-filament::button>
                            @else
                                <span class="inline-flex items-center px-2 py-1 mt-1 text-xs font-medium text-green-700 rounded-md bg-green-50 ring-1 ring-inset ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/20">
                                     <x-heroicon-s-play class="-ml-0.5 mr-1.5 h-4 w-4"/>
                                     Em Andamento
                                 </span>
                            @endif
                        </div>
                    </div>

                    {{-- Botões de Ação --}}
                    <div class="flex flex-wrap items-center justify-center gap-3 py-2 border-t border-gray-200 dark:border-white/10">
                        {{-- @if($isPaused)
                            <x-filament::button
                                wire:click="resumeTask"
                                icon="heroicon-m-play-circle"
                                color="success"
                                wire:loading.attr="disabled"
                                wire:target="resumeTask"
                                class="w-full sm:w-auto"
                            >
                                Retomar Tarefa
                                <x-filament::loading-indicator wire:loading wire:target="resumeTask" class="w-5 h-5"/>
                            </x-filament::button> --}}
                        @if(!$isPaused)
                            <div class="grid grid-cols-2 gap-2">
                                <x-filament::button
                                    x-on:click="showPauseModal = true"
                                    icon="heroicon-m-pause-circle"
                                    color="warning"
                                >
                                    Pausar Tarefa
                                </x-filament::button>

                                <x-filament::button
                                    x-on:click="isFinalizing = true; showScannerModal = true; scanErrorMessage = ''; $nextTick(() => startScanner());"
                                    icon="heroicon-m-camera"
                                    color="primary"
                                    class="w-full sm:w-auto"
                                >
                                    Finalizar Tarefa
                                </x-filament::button>
                            </div>
                        @endif
                    </div>
                </div>

            @else
                {{-- ============================================= --}}
                {{--    EXIBIÇÃO QUANDO NÃO HÁ TAREFA ATIVA      --}}
                {{-- ============================================= --}}
                <div class="flex flex-col items-center justify-center p-12 text-center border-2 border-gray-300 border-dashed rounded-lg dark:border-gray-600">
                    <x-heroicon-m-qr-code class="w-12 h-12 mx-auto text-gray-400"/>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">Nenhuma tarefa ativa</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Escaneie o QR Code de uma ordem/etapa para iniciar.</p>
                    <div class="mt-6">
                        {{-- Botão para abrir o Scanner (Usa $nextTick) --}}
                        <x-filament::button
                            x-on:click="showScannerModal = true; scanErrorMessage = ''; $nextTick(() => startScanner());"
                            icon="heroicon-m-camera"
                            class="w-full sm:w-auto"
                        >
                            Escanear QR Code
                        </x-filament::button>
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- ============================================= --}}
    {{--             MODAIS (Alpine.js)              --}}
    {{-- ============================================= --}}

    {{-- --- MODAL DO SCANNER --- --}}
    <div x-show="showScannerModal"
         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75"
         style="display: none;"
         x-on:keydown.escape.window="showScannerModal = false; stopScanner();"
         aria-labelledby="scanner-modal-title" role="dialog" aria-modal="true"
    >
        <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-xl dark:bg-gray-800"
             x-on:click.outside="showScannerModal = false; stopScanner();"
        >
            <h3 id="scanner-modal-title" class="mb-4 text-lg font-medium text-center text-gray-900 dark:text-white">Escanear QR Code</h3>
            <p class="mb-4 text-sm text-center text-gray-600 dark:text-gray-400">Posicione o QR Code na área abaixo:</p>

            {{-- Área do Leitor --}}
            <div id="qr-reader" style="width: 100%; max-width: 400px; margin: auto; border: 1px solid #ccc; min-height: 250px;">
                {{-- O JS do html5-qrcode vai renderizar aqui --}}
            </div>

                {{-- Opção manual de colar código --}}
            {{-- <div class="p-4 bg-gray-100 rounded-lg mt-4">
                    <h3 class="text-sm font-semibold mb-2 text-gray-700">Inserir QR Code manualmente</h3>
                    <div class="flex gap-2">
                        <input 
                            type="text" 
                            wire:model.defer="debugScannedQrCode" 
                            placeholder="Cole aqui o código criptografado do QR Code..." 
                            class="flex-1 border rounded px-3 py-2 text-sm"
                        >
                        <button 
                            wire:click="debugQrCode" 
                            class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded"
                        >
                            📋 Colar / Processar
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Use esta opção caso não tenha câmera disponível. Cole o conteúdo exato do código.
                    </p>
                </div>--}}

            {{-- Exibição de Erro do Scanner --}}
            <div id="qr-reader-results" class="mt-2 text-sm text-center text-red-600 dark:text-red-400" x-text="scanErrorMessage" x-show="scanErrorMessage"></div>

            {{-- Botão Fechar --}}
            <div class="mt-6 text-center">
                <x-filament::button id="close-scanner-modal" color="gray" x-on:click="showScannerModal = false; stopScanner();">
                    Fechar
                </x-filament::button>
            </div>
        </div>
    </div>

    {{-- --- MODAL DE PAUSA --- --}}
    {{-- CORREÇÃO: Removido x-data e @entangle daqui. O x-show agora usa a variável do escopo pai. --}}
    <div x-show="showPauseModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title-pause" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showPauseModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75"
                 aria-hidden="true" @click="showPauseModal = false; $wire.resetModalFields()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showPauseModal" x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 dark:bg-gray-800">
                <div>
                    <div class="mt-3 text-center sm:mt-0 sm:text-left">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="modal-title-pause">
                            Pausar Tarefa
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Informe o motivo da pausa e a quantidade produzida nesta sessão (se houver).
                            </p>
                        </div>
                    </div>
                </div>
                <form wire:submit.prevent="pauseTask" class="mt-5 space-y-4">
                    {{-- Select para Motivo da Pausa --}}
                    <div>
                        <label for="pause_reason_uuid" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Motivo da Pausa <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="selectedPauseReasonUuid" id="pause_reason_uuid" name="pause_reason_uuid"
                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 @error('selectedPauseReasonUuid') border-red-500 @enderror">
                            <option value="">Selecione um motivo...</option>
                            @foreach($availablePauseReasons as $uuid => $name)
                                <option value="{{ $uuid }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('selectedPauseReasonUuid') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="pause_quantity_produced" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Quantidade Produzida (nesta sessão)
                        </label>
                        <input type="number" step="any" wire:model.defer="pauseQuantityProduced" id="pause_quantity_produced" name="pause_quantity_produced"
                               placeholder="0.00"
                               class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 @error('pauseQuantityProduced') border-red-500 @enderror">
                        @error('pauseQuantityProduced') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="pause_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Observações (Opcional)
                        </label>
                        <textarea wire:model.defer="pauseNotes" id="pause_notes" name="pause_notes" rows="3"
                                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 @error('pauseNotes') border-red-500 @enderror"></textarea>
                        @error('pauseNotes') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="pauseTask"
                                class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-yellow-600 border border-transparent rounded-md shadow-sm hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:col-start-2 sm:text-sm dark:focus:ring-offset-gray-800">
                        <span wire:loading wire:target="pauseTask" class="mr-2">
                            <svg class="w-5 h-5 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                            Confirmar Pausa
                        </button>
                        <button type="button" @click="showPauseModal = false; $wire.resetModalFields()"
                                class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:col-start-1 sm:text-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- --- MODAL DE FINALIZAR --- --}}
    <div x-show="showFinishModal" x-transition
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75" style="display: none;"
         aria-labelledby="finish-modal-title" role="dialog" aria-modal="true">
        <div class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-xl dark:bg-gray-800"
             x-on:click.outside="showFinishModal = false; $wire.resetModalFields()">
            <h3 id="finish-modal-title" class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Finalizar Tarefa</h3>
            <form wire:submit.prevent="finishTask">
                <div class="space-y-4">
                    {{-- Quantidade Produzida na Sessão --}}
                    <div>
                        <label for="finish_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantidade Produzida (Nesta Sessão)</label>
                        <x-filament::input.wrapper :valid="!$errors->has('finishQuantityProduced')">
                            <x-filament::input type="number" wire:model="finishQuantityProduced" id="finish_quantity" min="0" step="any"/>
                        </x-filament::input.wrapper>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Informe quantas unidades você finalizou DURANTE esta sessão de trabalho.</p>
                        @error('finishQuantityProduced') <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Notas --}}
                    <div>
                        <label for="finish_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observações da Finalização (Opcional)</label>
                        <x-filament::input.wrapper :valid="!$errors->has('finishNotes')">
                            <x-filament::input
                                type="textarea"
                                wire:model.defer="finishNotes"
                                id="finish_notes"
                                rows="2"
                            />
                        </x-filament::input.wrapper>
                        @error('finishNotes') <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Botões do formulário de finalizar --}}
                <div class="flex justify-end gap-3 mt-6">
                    <x-filament::button type="button" color="gray" x-on:click="showFinishModal = false; $wire.resetModalFields()">
                        Cancelar
                    </x-filament::button>
                    <x-filament::button
                        type="submit"
                        color="success"
                        wire:loading.attr="disabled"
                        wire:target="finishTask"
                        x-bind:disabled="$wire.finishQuantityProduced === null || $wire.finishQuantityProduced < 0"
                    >
                        Confirmar Finalização
                        <x-filament::loading-indicator wire:loading wire:target="finishTask" class="w-5 h-5"/>
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============================================= --}}
    {{--             SCRIPTS (html5-qrcode)          --}}
    {{-- ============================================= --}}
    @push('scripts')
        {{-- Importa a biblioteca --}}
        <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

        {{-- Lógica do Scanner --}}
        <script>
            let html5QrCode = null; // Variável global para a instância do scanner
            let debounceTimer = null; // Timer para o debounce
            const DEBOUNCE_DELAY = 200; // Atraso em milissegundos (200ms) - ajuste conforme necessário
            let isProcessingScan = false;

            function onScanSuccess(decodedText, decodedResult) {
                if (isProcessingScan) {
                    console.log("Ignorando scan rápido, já processando.");
                    return;
                }

                console.log(`Code matched = ${decodedText}`, decodedResult);

                clearTimeout(debounceTimer);

                debounceTimer = setTimeout(() => {
                    console.log(`Debounced: Processando QR Code = ${decodedText}`);
                    isProcessingScan = true;
                    
                    // Disparamos ambos os eventos e deixamos o Alpine.js decidir o que fazer
                    window.dispatchEvent(new CustomEvent('qr-code-scanned', { detail: { decodedText: decodedText } }));
                    window.dispatchEvent(new CustomEvent('scan-success', { detail: { decodedText: decodedText } }));
                    
                    setTimeout(() => {
                        isProcessingScan = false;
                    }, 1000); // Pequeno delay para evitar re-scans imediatos
                }, DEBOUNCE_DELAY);
            }

            function onScanFailure(error) {
                if (!error.includes("NotFoundException")) {
                    console.warn(`Code scan error = ${error}`);
                }
            }

            function startScanner() {
                console.log('Função startScanner chamada.');
                const qrReaderElement = document.getElementById('qr-reader');
                if (!qrReaderElement) {
                    console.error("Elemento #qr-reader não encontrado.");
                    return;
                }
                stopScanner();
                try {
                    html5QrCode = new Html5Qrcode("qr-reader");
                    const config = {
                        fps: 5,
                        qrbox: (viewfinderWidth, viewfinderHeight) => {
                            let minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                            let qrboxSize = Math.floor(minEdge * 0.7);
                            return { width: qrboxSize, height: qrboxSize };
                        },
                        rememberLastUsedCamera: true,
                        supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
                    };
                    html5QrCode.start(
                        { facingMode: "environment" },
                        config,
                        onScanSuccess,
                        onScanFailure
                    ).then(() => {
                        console.log("Scanner iniciado com sucesso via start().");
                    }).catch((err) => {
                        console.error("Falha ao iniciar o scanner via start().", err);
                    });
                } catch (error) {
                    console.error("Erro ao criar ou configurar a instância Html5Qrcode.", error);
                    html5QrCode = null;
                }
            }

            function stopScanner() {
                console.log('Função stopScanner chamada.');
                clearTimeout(debounceTimer);
                isProcessingScan = false;
                if (!html5QrCode) {
                    console.log("Instância do scanner já é nula ou inválida.");
                    return;
                }
                const scannerInstanceToStop = html5QrCode;
                html5QrCode = null;
                console.log("Variável global html5QrCode definida como null.");
                if (scannerInstanceToStop && typeof scannerInstanceToStop.getState === 'function' && scannerInstanceToStop.getState() === Html5QrcodeScannerState.SCANNING) {
                    console.log("Tentando parar scanner ativo...");
                    scannerInstanceToStop.stop().then(() => {
                        console.log("Scanner parado com sucesso (stop resolved).");
                        const currentQrReaderElement = document.getElementById('qr-reader');
                        if (currentQrReaderElement) {
                            try { scannerInstanceToStop.clear(); console.log("Elemento #qr-reader limpo via clear()."); }
                            catch (e) { console.error("Erro durante clear() após stop bem-sucedido:", e); }
                        }
                    }).catch((err) => {
                        console.error("Falha ao parar o scanner (stop rejected).", err);
                        const currentQrReaderElement = document.getElementById('qr-reader');
                        if (currentQrReaderElement) {
                            try { scannerInstanceToStop.clear(); console.log("Elemento #qr-reader limpo via clear() (após falha no stop)."); }
                            catch (e) { console.error("Erro durante clear() após falha no stop:", e); }
                        }
                    });
                } else if (scannerInstanceToStop) {
                    console.log("Scanner não estava ativo ou estado inválido. Tentando limpar.");
                    const currentQrReaderElement = document.getElementById('qr-reader');
                    if (currentQrReaderElement) {
                        try { scannerInstanceToStop.clear(); console.log("Elemento #qr-reader limpo via clear() (scanner não estava ativo)."); }
                        catch (e) { console.error("Erro durante clear() (scanner não estava ativo):", e); }
                    }
                }
            }

            document.addEventListener('livewire:navigating', () => {
                console.log('Livewire navigating detectado, chamando stopScanner.');
                stopScanner();
            });

        </script>
    @endpush
</div>
