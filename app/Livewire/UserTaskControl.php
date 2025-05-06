<?php

namespace App\Livewire;

use App\Models\ProductionLog;
use App\Models\ProductionOrderItem;
use App\Models\ProductionStep;
use App\Models\UserCurrentTask;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Str;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed; // Certifique-se que este import está presente

class UserTaskControl extends Component
{
    public ?UserCurrentTask $currentTask = null;
    // public ?string $taskDetails = null; // Removido, informações são mais específicas agora
    public ?string $productName = null;
    public ?string $stepName = null;
    public ?string $orderNumber = null;
    public float $quantityPlanned = 0;
    public float $quantityProduced = 0; // Quantidade total atualizada do item
    public float $quantityRemaining = 0;
    public bool $isPaused = false;
    // public ?string $timeOnTask = null; // Removido, a computada cuida disso

    // Propriedades para modais
    public string $pauseReason = '';
    public ?float $pauseQuantityProduced = null; // Quantidade da sessão de pausa
    public string $pauseNotes = '';
    public ?float $finishQuantityProduced = null; // Quantidade da sessão final
    public string $finishNotes = '';

    /**
     * Listener para evento de QR Code escaneado vindo do JavaScript.
     *
     * @var array
     */
    protected $listeners = ['qrCodeScanned' => 'handleQrCodeScan']; // Mapeia evento JS para método PHP

    public function mount(): void
    {
        Log::info('UserTaskControl mount() INICIADO');
        $this->loadCurrentTask();
        Log::info('UserTaskControl mount() FINALIZADO');
    }

    /**
     * Carrega a tarefa atual (ativa ou pausada) do usuário logado.
     */
    public function loadCurrentTask(): void
    {
        $userId = Auth::id(); // Pega o ID (UUID) do usuário logado

        // Libera a tarefa anterior da memória se existir
        unset($this->currentTask);
        $this->currentTask = null;

        $this->currentTask = UserCurrentTask::with([
            'productionStep',
            // Caminho corrigido: Carrega o item, seu produto e sua ordem
            'productionOrderItem.product',
            'productionOrderItem.productionOrder',
            'workSlot'
        ])
            ->where('user_uuid', $userId)
            // Busca tarefas ativas OU pausadas para exibir o estado correto
            ->whereIn('status', ['active', 'paused'])
            ->first();

        if ($this->currentTask) {
            $step = $this->currentTask->productionStep;
            $item = $this->currentTask->productionOrderItem;
            $order = $item?->productionOrder;
            // Produto agora carregado corretamente através do item
            $product = $item?->product;

            $this->stepName = $step?->name ?? 'Etapa Desconhecida';
            $this->orderNumber = $order?->order_number ?? 'Ordem Desconhecida';
            $this->productName = $product?->name ?? 'Produto Desconhecido';
            $this->quantityPlanned = $item?->quantity_planned ?? 0;

            // Carrega a quantidade produzida ATUAL do item da ordem
            $this->quantityProduced = $item?->quantity_produced ?? 0;

            // Status de pausa vem diretamente da tarefa atual
            $this->isPaused = $this->currentTask->status === 'paused';

            $this->calculateQuantities(); // Recalcula o restante

            Log::info('Tarefa carregada', [
                'taskId' => $this->currentTask->uuid,
                'status' => $this->currentTask->status,
                'isPaused' => $this->isPaused
            ]);

        } else {
            Log::info('Nenhuma tarefa ativa ou pausada encontrada para o usuário.', ['userId' => $userId]);
            $this->resetTaskDetails();
        }
    }

    /**
     * Calcula a quantidade restante com base no planejado e produzido.
     */
    protected function calculateQuantities(): void
    {
        $this->quantityRemaining = max(0, $this->quantityPlanned - $this->quantityProduced);
    }

    /**
     * Propriedade computada para calcular o tempo total ativo da tarefa.
     * Usa os dados da tabela user_current_tasks para performance.
     * Atualiza a cada segundo.
     */
    #[Computed(persist: true, seconds: 1)]
    public function calculateTimeOnTask(): string
    {
        // Verifica se currentTask foi carregado antes de acessá-lo
        if (!$this->currentTask?->uuid) { // Verifica se a tarefa existe
            return '0s';
        }

        // Pega o tempo já acumulado na tarefa
        $totalSeconds = $this->currentTask->total_active_seconds ?? 0;

        // Se a tarefa está ATIVA, adiciona o tempo desde a última retomada
        if ($this->currentTask->status === 'active' && $this->currentTask->last_resumed_at) {
            try {
                // Garante que last_resumed_at é um objeto Carbon
                $lastResumed = Carbon::parse($this->currentTask->last_resumed_at);
                $totalSeconds += Carbon::now()->diffInSeconds($lastResumed);
            } catch (\Exception $e) {
                Log::error('Erro ao parsear last_resumed_at para cálculo de tempo', [
                    'taskId' => $this->currentTask->uuid,
                    'last_resumed_at' => $this->currentTask->last_resumed_at,
                    'error' => $e->getMessage()
                ]);
                // Retorna o tempo acumulado sem a sessão atual em caso de erro
            }
        }

        // Formata para exibição humana
        return $totalSeconds > 0 ? CarbonInterval::seconds($totalSeconds)->cascade()->forHumans(['short' => true]) : '0s';
    }

    /**
     * Reseta todas as propriedades relacionadas aos detalhes da tarefa.
     */
    protected function resetTaskDetails(): void
    {
        $this->currentTask = null;
        // $this->taskDetails = null; // Removido
        $this->productName = null;
        $this->stepName = null;
        $this->orderNumber = null;
        $this->quantityPlanned = 0;
        $this->quantityProduced = 0;
        $this->quantityRemaining = 0;
        $this->isPaused = false;
        // $this->timeOnTask = null; // Removido
        $this->resetModalFields(); // Garante que campos do modal também sejam limpos
    }

    /**
     * Reseta os campos dos modais de pausa e finalização.
     */
    protected function resetModalFields(): void
    {
        $this->pauseReason = '';
        $this->pauseQuantityProduced = null;
        $this->pauseNotes = '';
        $this->finishQuantityProduced = null;
        $this->finishNotes = '';
    }

    /**
     * Método chamado pelo listener quando o evento 'qr-code-scanned' é recebido do JS.
     *
     * @param array $eventData Dados do evento (espera-se ['detail']['decodedText'])
     */
    public function handleQrCodeScan(array $eventData): void
    {
        $scannedData = $eventData['detail']['decodedText'] ?? null;

        if (!$scannedData) {
            Log::warning('Evento qr-code-scanned recebido sem dados válidos.');
            Notification::make()->danger()->title('Erro de Leitura')->body('Não foi possível obter os dados do QR Code.')->send();
            return;
        }

        $this->processScanResult($scannedData);
    }


    /**
     * Processa os dados lidos do QR Code para iniciar uma nova tarefa.
     *
     * @param string $scannedData Os dados brutos lidos (espera-se "OrderItemUUID:StepUUID")
     */
    public function processScanResult(string $scannedData): void
    {
        Log::info('QR Code Data Recebido para Processamento:', ['data' => $scannedData]);
        DB::beginTransaction();
        try {
            // Regex para validar dois UUIDs separados por ':'
            $uuidPattern = '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}';
            if (!preg_match("/^({$uuidPattern}):({$uuidPattern})$/", $scannedData, $matches)) {
                Log::warning('Formato do QR Code não corresponde ao padrão UUID:UUID.', ['data' => $scannedData]);
                throw ValidationException::withMessages(['scan' => 'Formato do QR Code inválido. Esperado UUID:UUID.']);
            }

            $orderItemUuid = $matches[1];
            $stepUuid = $matches[2];

            Log::debug('UUIDs extraídos:', ['orderItemUuid' => $orderItemUuid, 'stepUuid' => $stepUuid]);

            // Verifica se os UUIDs são válidos (opcional, mas recomendado)
            if (!Str::isUuid($orderItemUuid) || !Str::isUuid($stepUuid)) {
                throw ValidationException::withMessages(['scan' => 'Dados do QR Code contêm UUIDs inválidos.']);
            }

            // Busca o item da ordem e a etapa usando os UUIDs
            $orderItem = ProductionOrderItem::where('uuid', $orderItemUuid)->first();
            $step = ProductionStep::where('uuid', $stepUuid)->first();

            // Verifica se o item da ordem encontrado realmente pertence à etapa esperada
            if (!$orderItem || !$step || $orderItem->production_step_uuid !== $step->uuid) {
                Log::error('Combinação Item/Etapa não encontrada ou inválida.', [
                    'orderItemUuid' => $orderItemUuid,
                    'stepUuid' => $stepUuid,
                    'orderItemFound' => !is_null($orderItem),
                    'stepFound' => !is_null($step),
                    'match' => $orderItem?->production_step_uuid === $step?->uuid
                ]);
                throw ValidationException::withMessages(['scan' => 'Item da Ordem ou Etapa não encontrada, ou não correspondem.']);
            }

            // Verifica se o usuário já tem uma tarefa ativa ou pausada
            $userId = Auth::id(); // Assume que Auth::id() retorna o UUID do usuário
            $existingTask = UserCurrentTask::where('user_uuid', $userId)
                ->whereIn('status', ['active', 'paused'])
                ->first();
            if ($existingTask) {
                throw ValidationException::withMessages(['scan' => 'Você já possui uma tarefa ativa ou pausada. Finalize-a antes de iniciar outra.']);
            }

            // Cria a nova tarefa atual para o usuário
            $newTask = UserCurrentTask::create([
                'user_uuid' => $userId,
                'production_order_item_uuid' => $orderItem->uuid,
                'production_step_uuid' => $step->uuid,
                'started_at' => now(),
                'last_resumed_at' => now(), // Define o início como a primeira retomada
                'status' => 'active',
                'total_active_seconds' => 0, // Inicia com 0 segundos
                // 'work_slot_uuid' => null, // Defina se souber onde o usuário está
            ]);

            // Cria o primeiro log de 'start' (Opcional, se ainda usar ProductionLog)
            // **Verifique se as chaves estrangeiras em production_logs são UUIDs**
            /*
            ProductionLog::create([
                'user_current_task_uuid' => $newTask->uuid, // Chave da tarefa
                'user_uuid' => $userId, // Chave do usuário
                'production_order_item_uuid' => $orderItem->uuid, // Chave do item
                'production_step_uuid' => $step->uuid, // Chave da etapa
                'action' => 'start',
                'quantity' => 0, // Quantidade da sessão
                // 'total_quantity_produced' => $orderItem->quantity_produced, // Total naquele momento (opcional)
                'details' => 'Tarefa iniciada via QR Code scan.',
                'created_at' => $newTask->started_at, // Usa o mesmo timestamp
            ]);
            */

            DB::commit();
            Log::info('Nova tarefa iniciada com sucesso.', ['taskId' => $newTask->uuid]);
            $this->loadCurrentTask(); // Recarrega os dados no componente
            Notification::make()->success()->title('Tarefa Iniciada!')->body('Você iniciou a tarefa ' . $this->orderNumber . ' - ' . $this->stepName)->send();
            $this->dispatch('scan-success'); // Dispara evento para JS fechar modal, etc.
            $this->dispatch('close-pause-modal');

        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Erro de validação ao processar QR Code:', ['error' => $e->getMessage()]);
            Notification::make()->danger()->title('Erro no QR Code')->body($e->getMessage())->send();
            $this->dispatch('scan-error', message: $e->getMessage()); // Informa JS sobre o erro
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro inesperado ao processar QR Code:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            Notification::make()->danger()->title('Erro Inesperado')->body('Ocorreu um erro ao iniciar a tarefa. Tente novamente.')->send();
            $this->dispatch('scan-error', message: 'Erro inesperado no servidor.'); // Informa JS sobre o erro
        }
    }

    /**
     * Pausa a tarefa ativa atual.
     */
    public function pauseTask(): void
    {
        // Validações iniciais
        if (!$this->currentTask || $this->currentTask->status !== 'active') {
            Notification::make()->warning()->title('Ação Inválida')->body('Não há tarefa ativa para pausar.')->send();
            return;
        }
        if (empty($this->pauseReason)) {
            Notification::make()->danger()->title('Erro na Pausa')->body('O motivo da pausa é obrigatório.')->send();
            return;
        }
        $quantityProducedSession = $this->pauseQuantityProduced ?? 0; // Quantidade da sessão
        if (!is_numeric($quantityProducedSession) || $quantityProducedSession < 0) {
            Notification::make()->danger()->title('Erro na Pausa')->body('Quantidade produzida na sessão inválida.')->send();
            return;
        }

        DB::beginTransaction();
        try {
            $now = now();

            // --- Atualizações em UserCurrentTask ---
            $durationSeconds = 0;
            if ($this->currentTask->last_resumed_at) {
                try {
                    // Garante que last_resumed_at é parseado corretamente
                    $lastResumed = Carbon::parse($this->currentTask->last_resumed_at);
                    // Calcula a diferença, garante que é não-negativa (abs é provavelmente redundante, mas seguro)
                    // Usar 'false' no diffInSeconds para verificar se a diferença pode ser negativa, depois aplicar abs()
                    $durationSeconds = abs($now->diffInSeconds($lastResumed, false));
                } catch (\Exception $e) {
                    Log::error('Erro ao calcular duração da sessão em pauseTask', ['taskId' => $this->currentTask->uuid, 'error' => $e->getMessage()]);
                    // Considerar lançar exceção para rollback ou tratar o erro de outra forma
                    throw new \RuntimeException("Falha ao calcular duração da sessão: " . $e->getMessage(), 0, $e);
                }
            }

            $existingTotalSeconds = (int) ($this->currentTask->total_active_seconds ?? 0);

// Calcula o novo total
            $newTotalActiveSeconds = $existingTotalSeconds + $durationSeconds;

// CRÍTICO: Garante que o valor final seja um inteiro não negativo
            $newTotalActiveSeconds = max(0, (int) $newTotalActiveSeconds);

            $this->currentTask->update([
                'status' => 'paused',
                'last_pause_at' => $now, // Coluna da migration
                'last_pause_reason' => $this->pauseReason,
                'total_active_seconds' => $newTotalActiveSeconds, // <-- Salva o valor inteiro sanitizado
                'last_resumed_at' => null // Limpa a última retomada
            ]);
            // --- Fim Atualizações em UserCurrentTask ---

            // --- Atualização em ProductionOrderItem ---
            // Busca o total atual REAL do item da ordem ANTES de adicionar a sessão
            $currentItemTotalQuantity = $this->currentTask->productionOrderItem->quantity_produced ?? 0;
            $newTotalQuantityForItem = $currentItemTotalQuantity + $quantityProducedSession;

            // Atualiza o item da ordem IMEDIATAMENTE ao pausar
            $this->currentTask->productionOrderItem()->update([
                'quantity_produced' => $newTotalQuantityForItem
            ]);
            Log::info('Quantidade atualizada no item da ordem ao pausar.', ['orderItemId' => $this->currentTask->production_order_item_uuid, 'newTotal' => $newTotalQuantityForItem]);
            // --- Fim Atualização em ProductionOrderItem ---

            // --- Lógica de ProductionLog (Opcional) ---
            // **Verifique se as chaves estrangeiras em production_logs são UUIDs**
            /*
            ProductionLog::create([
                'user_current_task_uuid' => $this->currentTask->uuid,
                'user_uuid' => Auth::id(),
                'production_order_item_uuid' => $this->currentTask->production_order_item_uuid,
                'production_step_uuid' => $this->currentTask->production_step_uuid,
                'action' => 'pause',
                'quantity' => $quantityProducedSession, // Apenas a quantidade da sessão
                // 'total_quantity_produced' => $newTotalQuantityForItem, // Opcional: total naquele momento
                'details' => $this->pauseReason . ($this->pauseNotes ? ' - Obs: ' . $this->pauseNotes : ''),
                'created_at' => $now,
            ]);
            */
            // --- Fim Lógica de ProductionLog ---

            DB::commit();

            Log::info('Tarefa pausada.', ['taskId' => $this->currentTask->uuid, 'reason' => $this->pauseReason]);

            // Força o recarregamento e re-renderização
            $this->loadCurrentTask();
            $this->resetModalFields();
            $this->dispatch('$refresh'); // Garante que o Livewire re-renderize

            Notification::make()->info()->title('Tarefa Pausada')->body('A tarefa foi pausada. Motivo: ' . $this->pauseReason)->send();
            $this->dispatch('close-pause-modal');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao pausar tarefa:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            Notification::make()->danger()->title('Erro ao Pausar')->body('Não foi possível pausar a tarefa.')->send();
        }
    }

    /**
     * Retoma uma tarefa que estava pausada.
     */
    public function resumeTask(): void
    {
        // Verifica se a tarefa existe e está pausada
        if (!$this->currentTask || $this->currentTask->status !== 'paused') {
            Notification::make()->warning()->title('Ação Inválida')->body('Não há tarefa pausada para retomar.')->send();
            return;
        }

        DB::beginTransaction();
        try {
            $now = now();

            // --- Atualizações em UserCurrentTask ---
            $this->currentTask->update([
                'status' => 'active',
                'last_resumed_at' => $now, // Define quando retomou
                'last_pause_at' => null, // Limpa a pausa
                'last_pause_reason' => null, // Limpa o motivo
            ]);
            // --- Fim Atualizações em UserCurrentTask ---

            // --- Lógica de ProductionLog (Opcional) ---
            // **Verifique se as chaves estrangeiras em production_logs são UUIDs**
            /*
            ProductionLog::create([
                'user_current_task_uuid' => $this->currentTask->uuid,
                'user_uuid' => Auth::id(),
                'production_order_item_uuid' => $this->currentTask->production_order_item_uuid,
                'production_step_uuid' => $this->currentTask->production_step_uuid,
                'action' => 'resume',
                'quantity' => 0, // Nenhuma quantidade produzida ao retomar
                // 'total_quantity_produced' => $this->currentTask->productionOrderItem->quantity_produced, // Opcional
                'details' => 'Tarefa retomada.',
                'created_at' => $now,
            ]);
            */
            // --- Fim Lógica de ProductionLog ---

            DB::commit();
            Log::info('Tarefa retomada.', ['taskId' => $this->currentTask->uuid]);

            // Força o recarregamento e re-renderização
            $this->loadCurrentTask();
            $this->dispatch('$refresh');

            Notification::make()->success()->title('Tarefa Retomada!')->send();
            $this->dispatch('close-pause-modal');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao retomar tarefa:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            Notification::make()->danger()->title('Erro ao Retomar')->body('Não foi possível retomar a tarefa.')->send();
        }
    }

    /**
     * Finaliza a tarefa atual (ativa ou pausada).
     */
    public function finishTask(): void
    {
        // Verifica se a tarefa existe e está ativa ou pausada
        if (!$this->currentTask || !in_array($this->currentTask->status, ['active', 'paused'])) {
            Notification::make()->warning()->title('Ação Inválida')->body('Não há tarefa ativa ou pausada para finalizar.')->send();
            return;
        }
        $quantityProducedSession = $this->finishQuantityProduced ?? 0; // Quantidade da sessão final
        if (!is_numeric($quantityProducedSession) || $quantityProducedSession < 0) {
            Notification::make()->danger()->title('Erro na Finalização')->body('Quantidade produzida na sessão inválida.')->send();
            $this->dispatch('close-pause-modal');
            return;
        }

        DB::beginTransaction();
        try {
            $now = now();
// Garante que o total existente seja tratado como inteiro
            $newTotalActiveSeconds = (int) ($this->currentTask->total_active_seconds ?? 0);

// Se estava ativa, calcula a duração da última sessão
            if ($this->currentTask->status === 'active' && $this->currentTask->last_resumed_at) {
                try {
                    $lastResumed = Carbon::parse($this->currentTask->last_resumed_at);
                    // Calcula a diferença, garante não-negativa
                    $durationSeconds = abs($now->diffInSeconds($lastResumed, false));
                    $newTotalActiveSeconds += $durationSeconds;
                } catch (\Exception $e) {
                    Log::error('Erro ao calcular duração da sessão em finishTask', ['taskId' => $this->currentTask->uuid, 'error' => $e->getMessage()]);
                    // Considerar lançar exceção para rollback ou tratar o erro de outra forma
                    throw new \RuntimeException("Falha ao calcular duração da sessão final: " . $e->getMessage(), 0, $e);
                }
            }

// CRÍTICO: Garante que o valor final seja um inteiro não negativo
            $newTotalActiveSeconds = max(0, (int) $newTotalActiveSeconds);

// --- Atualizações em UserCurrentTask ---
// Marca a tarefa como finalizada
            $this->currentTask->update([
                'status' => 'finished', // Define status final
                'total_active_seconds' => $newTotalActiveSeconds, // <-- Salva o valor inteiro sanitizado
                'last_resumed_at' => null, // Limpa retomada
                'last_pause_at' => null, // Limpa pausa
                'last_pause_reason' => null, // Limpa motivo
                // 'finished_at' => $now // Se tiver uma coluna específica para isso
            ]);
            // --- Fim Atualizações em UserCurrentTask ---

            // --- Atualização em ProductionOrderItem ---
            // Busca o total atual REAL do item da ordem ANTES de adicionar a sessão final
            $currentItemTotalQuantity = $this->currentTask->productionOrderItem->quantity_produced ?? 0;
            $finalTotalQuantityForItem = $currentItemTotalQuantity + $quantityProducedSession;

            // Atualiza o item da ordem com o total final
            $this->currentTask->productionOrderItem()->update([
                'quantity_produced' => $finalTotalQuantityForItem
            ]);
            Log::info('Quantidade final atualizada no item da ordem.', ['orderItemId' => $this->currentTask->production_order_item_uuid, 'newTotal' => $finalTotalQuantityForItem]);
            // --- Fim Atualização em ProductionOrderItem ---

            // --- Lógica de ProductionLog (Opcional) ---
            // **Verifique se as chaves estrangeiras em production_logs são UUIDs**
            /*
            ProductionLog::create([
                'user_current_task_uuid' => $this->currentTask->uuid,
                'user_uuid' => Auth::id(),
                'production_order_item_uuid' => $this->currentTask->production_order_item_uuid,
                'production_step_uuid' => $this->currentTask->production_step_uuid,
                'action' => 'finish',
                'quantity' => $quantityProducedSession, // Quantidade da última sessão
                // 'total_quantity_produced' => $finalTotalQuantityForItem, // Opcional
                'details' => 'Tarefa finalizada.' . ($this->finishNotes ? ' Obs: ' . $this->finishNotes : ''),
                'created_at' => $now,
            ]);
            */
            // --- Fim Lógica de ProductionLog ---

            DB::commit();
            Log::info('Tarefa finalizada com sucesso.', ['taskId' => $this->currentTask->uuid]);

            // Reseta tudo após sucesso
            $this->resetTaskDetails();
            // $this->resetModalFields(); // resetTaskDetails já chama isso
            $this->dispatch('$refresh'); // Garante re-renderização

            Notification::make()->success()->title('Tarefa Finalizada!')->body('A tarefa foi concluída com sucesso.')->send();
            $this->dispatch('close-pause-modal');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao finalizar tarefa:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            Notification::make()->danger()->title('Erro ao Finalizar')->body('Não foi possível finalizar a tarefa.')->send();
        }
    }

    /**
     * Renderiza a view do componente.
     */
    public function render(): View
    {
        return view('livewire.user-task-control');
    }
}
