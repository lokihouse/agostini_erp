{{-- resources/views/livewire/user-task-control.blade.php --}}
<div x-data="{
        showScannerModal: false,
        showPauseModal: false,
        showFinishModal: false,
        scanErrorMessage: '' // Para exibir erros do scanner no modal
     }"
     x-on:scan-success.window="showScannerModal = false; stopScanner(); scanErrorMessage = '';" {{-- Fecha modal no sucesso --}}
     x-on:scan-error.window="scanErrorMessage = $event.detail.message;" {{-- Mostra erro no modal --}}
     x-on:qr-code-scanned.window="$wire.call('processScanResult', $event.detail.decodedText); scanErrorMessage = '';" {{-- Chama Livewire no evento --}}
     class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
>
    {{-- Cabeçalho --}}
    <div class="fi-section-header-ctn border-b border-gray-200 px-6 py-4 dark:border-white/10">
        <div class="fi-section-header flex flex-col gap-y-2 sm:flex-row sm:items-center">
            <div class="grid flex-1 gap-y-1">
                <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                    Minha Tarefa Atual
                </h3>
                <p class="fi-section-header-description text-sm text-gray-500 dark:text-gray-400">
                    Gerencie sua atividade de produção atual.
                </p>
            </div>
        </div>
    </div>

    {{-- Conteúdo Principal --}}
    <div class="fi-section-content-ctn">
        <div class="fi-section-content p-6">

            @if ($currentTask)
                {{-- ============================================= --}}
                {{--      EXIBIÇÃO QUANDO HÁ TAREFA ATIVA        --}}
                {{-- ============================================= --}}
                <div class="space-y-4">
                    {{-- Detalhes da Tarefa --}}
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-2 text-sm sm:grid-cols-3">
                            <div class="sm:col-span-1">
                                <dt class="font-medium text-gray-500 dark:text-gray-400">Ordem:</dt>
                                <dd class="font-semibold text-gray-900 dark:text-white">{{ $orderNumber }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="font-medium text-gray-500 dark:text-gray-400">Produto:</dt>
                                <dd class="font-semibold text-gray-900 dark:text-white">{{ $productName }}</dd>
                            </div>
                            <div class="sm:col-span-3">
                                <dt class="font-medium text-gray-500 dark:text-gray-400">Etapa:</dt>
                                <dd class="font-semibold text-gray-900 dark:text-white">{{ $stepName }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Progresso e Tempo --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        {{-- Quantidades --}}
                        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <h4 class="mb-2 text-center text-sm font-medium text-gray-500 dark:text-gray-400">Progresso</h4>
                            <div class="flex items-baseline justify-center gap-x-2">
                                <span class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ number_format($quantityProduced, 0, ',', '.') }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">/ {{ number_format($quantityPlanned, 0, ',', '.') }}</span>
                            </div>
                            <p class="mt-1 text-center text-xs text-gray-500 dark:text-gray-400">Produzido / Planejado</p>
                            <p class="mt-2 text-center text-sm font-semibold {{ $quantityRemaining > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-green-600 dark:text-green-400' }}">
                                Restante: {{ number_format($quantityRemaining, 0, ',', '.') }}
                            </p>
                        </div>

                        {{-- Tempo na Tarefa --}}
                        <div class="flex flex-col items-center justify-center rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:col-span-2">
                            <h4 class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">Tempo na Tarefa</h4>
                            <div class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                                {{-- A propriedade computada $this->calculateTimeOnTask é atualizada automaticamente --}}
                                {{ $this->calculateTimeOnTask }}
                            </div>
                            @if($isPaused)
                                <span class="mt-1 inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20 dark:bg-yellow-400/10 dark:text-yellow-500 dark:ring-yellow-400/20">
                                     <x-heroicon-s-pause class="-ml-0.5 mr-1.5 h-4 w-4"/>
                                     Pausado
                                 </span>
                            @else
                                <span class="mt-1 inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/20">
                                     <x-heroicon-s-play class="-ml-0.5 mr-1.5 h-4 w-4"/>
                                     Em Andamento
                                 </span>
                            @endif
                        </div>
                    </div>

                    {{-- Botões de Ação --}}
                    <div class="mt-6 flex flex-wrap items-center justify-center gap-3 border-t border-gray-200 pt-6 dark:border-white/10">
                        @if($isPaused)
                            {{-- Botão Retomar --}}
                            <x-filament::button
                                wire:click="resumeTask"
                                icon="heroicon-m-play-circle"
                                color="success"
                                wire:loading.attr="disabled"
                                wire:target="resumeTask"
                            >
                                Retomar Tarefa
                                <x-filament::loading-indicator wire:loading wire:target="resumeTask" class="h-5 w-5"/>
                            </x-filament::button>
                        @else
                            {{-- Botão Pausar (Abre modal Alpine) --}}
                            <x-filament::button
                                x-on:click="showPauseModal = true"
                                icon="heroicon-m-pause-circle"
                                color="warning"
                            >
                                Pausar Tarefa
                            </x-filament::button>

                            {{-- Botão Finalizar (Abre modal Alpine) --}}
                            <x-filament::button
                                x-on:click="showFinishModal = true"
                                icon="heroicon-m-check-circle"
                                color="success"
                            >
                                Finalizar Tarefa
                            </x-filament::button>
                        @endif
                    </div>
                </div>

            @else
                {{-- ============================================= --}}
                {{--    EXIBIÇÃO QUANDO NÃO HÁ TAREFA ATIVA      --}}
                {{-- ============================================= --}}
                <div class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 p-12 text-center dark:border-gray-600">
                    <x-heroicon-m-qr-code class="mx-auto h-12 w-12 text-gray-400"/>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">Nenhuma tarefa ativa</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Escaneie o QR Code de uma ordem/etapa para iniciar.</p>
                    <div class="mt-6">
                        {{-- Botão para abrir o Scanner (Usa $nextTick) --}}
                        <x-filament::button
                            x-on:click="showScannerModal = true; scanErrorMessage = ''; $nextTick(() => startScanner());"
                            icon="heroicon-m-camera"
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
        <div class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800"
             x-on:click.outside="showScannerModal = false; stopScanner();"
        >
            <h3 id="scanner-modal-title" class="mb-4 text-lg font-medium text-center text-gray-900 dark:text-white">Escanear QR Code</h3>
            <p class="mb-4 text-sm text-center text-gray-600 dark:text-gray-400">Posicione o QR Code na área abaixo:</p>

            {{-- Área do Leitor --}}
            <div id="qr-reader" style="width: 100%; max-width: 400px; margin: auto; border: 1px solid #ccc; min-height: 250px;">
                {{-- O JS do html5-qrcode vai renderizar aqui --}}
            </div>

            {{-- Exibição de Erro do Scanner --}}
            <div id="qr-reader-results" class="mt-2 text-center text-sm text-red-600 dark:text-red-400" x-text="scanErrorMessage" x-show="scanErrorMessage"></div>

            {{-- Botão Fechar --}}
            <div class="mt-6 text-center">
                <x-filament::button color="gray" x-on:click="showScannerModal = false; stopScanner();">
                    Fechar
                </x-filament::button>
            </div>
        </div>
    </div>

    {{-- --- MODAL DE PAUSA --- --}}
    <div x-show="showPauseModal" x-transition
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75" style="display: none;"
         aria-labelledby="pause-modal-title" role="dialog" aria-modal="true">
        <div class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800"
             x-on:click.outside="showPauseModal = false">
            <h3 id="pause-modal-title" class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Pausar Tarefa</h3>
            <form wire:submit.prevent="pauseTask">
                <div class="space-y-4">
                    {{-- Motivo --}}
                    <div>
                        <x-filament::input.wrapper :valid="!$errors->has('pauseReason')">
                            <x-filament::input.select wire:model="pauseReason" id="pause_reason" required>
                                <option value="" disabled>Selecione o Motivo...</option>
                                <option value="banheiro">Pausa - Banheiro</option>
                                <option value="almoco">Pausa - Almoço/Refeição</option>
                                <option value="cafe">Pausa - Café/Descanso</option>
                                <option value="limpeza">Pausa - Limpeza/Organização</option>
                                <option value="material">Pausa - Aguardando Material</option>
                                <option value="manutencao">Pausa - Aguardando Manutenção</option>
                                <option value="fim_expediente">Pausa - Fim de Expediente</option>
                                <option value="outro">Pausa - Outro Motivo</option>
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                        @error('pauseReason') <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Quantidade Produzida na Sessão --}}
                    <div>
                        <label for="pause_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantidade Produzida (Nesta Sessão)</label>
                        <x-filament::input.wrapper :valid="!$errors->has('pauseQuantityProduced')">
                            <x-filament::input type="number" wire:model="pauseQuantityProduced" id="pause_quantity" min="0" step="any"/>
                        </x-filament::input.wrapper>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Informe quantas unidades você finalizou ANTES de iniciar esta pausa.</p>
                        @error('pauseQuantityProduced') <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Notas --}}
                    <div>
                        <label for="pause_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observações da Pausa (Opcional)</label>
                        {{-- Textarea Corrigido --}}
                        <x-filament::input.wrapper :valid="!$errors->has('pauseNotes')">
                            <x-filament::input
                                type="textarea"
                                wire:model.defer="pauseNotes"
                                id="pause_notes"
                                rows="2"
                            />
                        </x-filament::input.wrapper>
                        @error('pauseNotes') <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Botões do formulário de pausa --}}
                <div class="mt-6 flex justify-end gap-3">
                    <x-filament::button type="button" color="gray" x-on:click="showPauseModal = false">
                        Cancelar
                    </x-filament::button>
                    <x-filament::button
                        type="submit"
                        color="warning"
                        wire:loading.attr="disabled"
                        wire:target="pauseTask"
                        x-bind:disabled="!$wire.pauseReason"
                    >
                        Confirmar Pausa
                        <x-filament::loading-indicator wire:loading wire:target="pauseTask" class="h-5 w-5"/>
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>

    {{-- --- MODAL DE FINALIZAR --- --}}
    <div x-show="showFinishModal" x-transition
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75" style="display: none;"
         aria-labelledby="finish-modal-title" role="dialog" aria-modal="true">
        <div class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800"
             x-on:click.outside="showFinishModal = false">
            <h3 id="finish-modal-title" class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Finalizar Tarefa</h3>
            <form wire:submit.prevent="finishTask">
                <div class="space-y-4">
                    {{-- Quantidade Produzida na Sessão --}}
                    <div>
                        <label for="finish_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantidade Produzida (Nesta Sessão)</label>
                        <x-filament::input.wrapper :valid="!$errors->has('finishQuantityProduced')">
                            <x-filament::input type="number" wire:model="finishQuantityProduced" id="finish_quantity" min="0" step="any" required />
                        </x-filament::input.wrapper>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Informe quantas unidades você finalizou DURANTE esta sessão de trabalho.</p>
                        @error('finishQuantityProduced') <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Notas --}}
                    <div>
                        <label for="finish_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observações da Finalização (Opcional)</label>
                        {{-- Textarea Corrigido --}}
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
                <div class="mt-6 flex justify-end gap-3">
                    <x-filament::button type="button" color="gray" x-on:click="showFinishModal = false">
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
                        <x-filament::loading-indicator wire:loading wire:target="finishTask" class="h-5 w-5"/>
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
            const DEBOUNCE_DELAY = 1000; // Atraso em milissegundos (1 segundo) - ajuste conforme necessário
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
                    window.dispatchEvent(new CustomEvent('qr-code-scanned', { detail: { decodedText: decodedText } }));
                    stopScanner();
                    setTimeout(() => {
                        isProcessingScan = false;
                    }, 500);
                }, DEBOUNCE_DELAY);
            }

            // Função chamada em caso de falha na leitura
            function onScanFailure(error) {
                // Evita poluir o console com erros comuns de "não encontrado"
                if (!error.includes("NotFoundException")) {
                    console.warn(`Code scan error = ${error}`);
                }
                // Você pode opcionalmente atualizar a UI aqui se quiser mostrar erros de leitura
                // document.getElementById('qr-reader-results').innerText = `Erro: ${error}`;
            }

            // Função para iniciar o scanner
            function startScanner() {
                console.log('Função startScanner chamada.');
                const qrReaderElement = document.getElementById('qr-reader');
                if (!qrReaderElement) {
                    console.error("Elemento #qr-reader não encontrado.");
                    return;
                }

                // Garante que qualquer instância anterior seja limpa antes de iniciar uma nova
                stopScanner(); // Chama stopScanner para limpar qualquer estado anterior

                try {
                    // Cria uma NOVA instância do scanner
                    html5QrCode = new Html5Qrcode("qr-reader"); // Usando a classe base

                    const config = {
                        fps: 10, // Taxa de quadros por segundo para scan
                        qrbox: (viewfinderWidth, viewfinderHeight) => {
                            // Define o tamanho da caixa de scan (ex: 70% do menor lado do viewfinder)
                            let minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                            let qrboxSize = Math.floor(minEdge * 0.7);
                            return { width: qrboxSize, height: qrboxSize };
                        },
                        rememberLastUsedCamera: true, // Lembra a última câmera usada
                        supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA] // Força o uso da câmera
                    };

                    // Inicia o scanner
                    html5QrCode.start(
                        { facingMode: "environment" }, // Tenta usar a câmera traseira ('environment') primeiro
                        config,
                        onScanSuccess, // Função de callback para sucesso
                        onScanFailure  // Função de callback para falha
                    ).then(() => {
                        console.log("Scanner iniciado com sucesso via start().");
                    }).catch((err) => {
                        console.error("Falha ao iniciar o scanner via start().", err);
                        // Poderia tentar com 'facingMode: "user"' (câmera frontal) como fallback aqui
                    });

                } catch (error) {
                    console.error("Erro ao criar ou configurar a instância Html5Qrcode.", error);
                    // Limpa a variável global em caso de erro na inicialização
                    html5QrCode = null;
                }
            }

            // Função para parar o scanner (Versão Robusta)
            function stopScanner() {
                console.log('Função stopScanner chamada.');

                clearTimeout(debounceTimer);
                isProcessingScan = false;

                if (!html5QrCode) {
                    console.log("Instância do scanner já é nula ou inválida. Nada a fazer.");
                    return;
                }

                const scannerInstanceToStop = html5QrCode;
                html5QrCode = null; // Anula a referência global imediatamente
                console.log("Variável global html5QrCode definida como null.");

                // Verifica se a instância era válida e estava escaneando
                if (scannerInstanceToStop && typeof scannerInstanceToStop.getState === 'function' && scannerInstanceToStop.getState() === Html5QrcodeScannerState.SCANNING) {
                    console.log("Tentando parar scanner ativo...");
                    scannerInstanceToStop.stop().then(() => {
                        console.log("Scanner parado com sucesso (stop resolved).");
                        // Tenta limpar o elemento após parar
                        const currentQrReaderElement = document.getElementById('qr-reader');
                        if (currentQrReaderElement) {
                            try {
                                scannerInstanceToStop.clear(); // Limpa o conteúdo da div #qr-reader
                                console.log("Elemento #qr-reader limpo via clear().");
                            } catch (e) { console.error("Erro durante clear() após stop bem-sucedido:", e); }
                        } else { console.log("Elemento #qr-reader não encontrado após stop, clear() não chamado."); }
                    }).catch((err) => {
                        console.error("Falha ao parar o scanner (stop rejected).", err);
                        // Mesmo com falha no stop, tenta limpar se o elemento existir
                        const currentQrReaderElement = document.getElementById('qr-reader');
                        if (currentQrReaderElement) {
                            try {
                                scannerInstanceToStop.clear();
                                console.log("Elemento #qr-reader limpo via clear() (após falha no stop).");
                            } catch (e) { console.error("Erro durante clear() após falha no stop:", e); }
                        } else { console.log("Elemento #qr-reader não encontrado após falha no stop, clear() não chamado."); }
                    });
                } else if (scannerInstanceToStop) {
                    console.log("Scanner não estava ativo (getState() !== SCANNING) ou estado inválido. Tentando limpar.");
                    // Se não estava escaneando, apenas tenta limpar
                    const currentQrReaderElement = document.getElementById('qr-reader');
                    if (currentQrReaderElement) {
                        try {
                            scannerInstanceToStop.clear();
                            console.log("Elemento #qr-reader limpo via clear() (scanner não estava ativo).");
                        } catch (e) { console.error("Erro durante clear() (scanner não estava ativo):", e); }
                    } else { console.log("Elemento #qr-reader não encontrado (scanner não estava ativo), clear() não chamado."); }
                } else {
                    // Isso não deve acontecer devido à verificação inicial, mas é um fallback
                    console.log("Instância do scanner era inválida ao verificar estado.");
                }
            }

            // Garante que o scanner pare se o usuário navegar para fora da página
            document.addEventListener('livewire:navigating', () => {
                console.log('Livewire navigating detectado, chamando stopScanner.');
                stopScanner();
            });

            // Opcional: Limpa o scanner se o modal for fechado por outros meios
            // (já coberto por x-on:keydown e x-on:click.outside, mas pode ser redundante aqui)
            // const observer = new MutationObserver((mutationsList) => {
            //     for(let mutation of mutationsList) {
            //         if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
            //             if (mutation.target.style.display === 'none' && html5QrCode) {
            //                 console.log('Modal do scanner ficou oculto, chamando stopScanner.');
            //                 stopScanner();
            //             }
            //         }
            //     }
            // });
            // const scannerModalElement = document.querySelector('[x-show="showScannerModal"]');
            // if (scannerModalElement) {
            //     observer.observe(scannerModalElement, { attributes: true });
            // }

        </script>
    @endpush
</div>
