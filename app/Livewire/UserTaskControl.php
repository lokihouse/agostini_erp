<?php

namespace App\Livewire;

use App\Models\ProductionOrderLog; // Mantenha se ainda usar para outros logs
use App\Models\ProductionOrderItem;
use App\Models\ProductionStep;
use App\Models\UserCurrentTask;
use App\Models\PauseReason;
use App\Models\TaskPauseLog;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;

class UserTaskControl extends Component
{
    public ?UserCurrentTask $currentTask = null;
    public ?string $productName = null;
    public ?string $stepName = null;
    public ?string $orderNumber = null;
    public float $quantityPlanned = 0;
    public float $quantityProduced = 0;
    public float $quantityRemaining = 0;
    public bool $isPaused = false;

    public ?string $selectedPauseReasonUuid = null;
    public array $availablePauseReasons = [];

    public ?float $pauseQuantityProduced = null;
    public string $pauseNotes = '';
    public ?float $finishQuantityProduced = null;
    public string $finishNotes = '';

    public string $debugScannedQrCode = '';

    protected $listeners = ['qrCodeScanned' => 'handleQrCodeScan'];

    public function mount(): void
    {
        Log::info('UserTaskControl mount() INICIADO');
        $this->loadCurrentTask();
        $this->loadAvailablePauseReasons();
        Log::info('UserTaskControl mount() FINALIZADO');
    }

    protected function loadAvailablePauseReasons(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->availablePauseReasons = [];
            return;
        }

        $query = PauseReason::query()->where('is_active', true);

        if ($user->company_id) {
            $query->where(function ($q) use ($user) {
                $q->whereNull('company_id')
                    ->orWhere('company_id', $user->company_id);
            });
        } else {
            $query->whereNull('company_id');
        }

        $this->availablePauseReasons = $query->orderBy('name')->pluck('name', 'uuid')->all();
    }

    public function loadCurrentTask(): void
    {
        $userId = Auth::id();
        $this->currentTask = null;

        $this->currentTask = UserCurrentTask::with([
            'productionStep',
            'productionOrderItem.product',
            'productionOrderItem.productionOrder',
            'workSlot',
            'lastPauseReasonDetail'
        ])
            ->where('user_uuid', $userId)
            ->whereIn('status', ['active', 'paused'])
            ->first();

        if ($this->currentTask) {
            $step = $this->currentTask->productionStep;
            $item = $this->currentTask->productionOrderItem;
            $order = $item?->productionOrder;
            $product = $item?->product;

            $this->stepName = $step?->name ?? 'Etapa Desconhecida';
            $this->orderNumber = $order?->order_number ?? 'Ordem Desconhecida';
            $this->productName = $product?->name ?? 'Produto Desconhecido';
            $this->quantityPlanned = $item?->quantity_planned ?? 0;
            $this->quantityProduced = $item?->quantity_produced ?? 0;
            $this->isPaused = $this->currentTask->status === 'paused';

            $this->calculateQuantities();

            Log::info('UserTaskControl: Tarefa carregada', [
                'taskId' => $this->currentTask->uuid,
                'status' => $this->currentTask->status,
                'isPaused' => $this->isPaused,
                'lastPauseReason' => $this->currentTask->lastPauseReasonDetail?->name
            ]);
        } else {
            Log::info('UserTaskControl: Nenhuma tarefa ativa ou pausada encontrada para o usuário.', ['userId' => $userId]);
            $this->resetTaskDetails();
        }
    }

    protected function calculateQuantities(): void
    {
        $this->quantityRemaining = max(0, $this->quantityPlanned - $this->quantityProduced);
    }

    #[Computed(persist: true, seconds: 1)]
    public function calculateTimeOnTask(): string
    {
        if (!$this->currentTask?->uuid) {
            return '0s';
        }
        $totalSeconds = $this->currentTask->total_active_seconds ?? 0;

        if ($this->currentTask->status === 'active' && $this->currentTask->last_resumed_at) {
            try {
                $lastResumed = Carbon::parse($this->currentTask->last_resumed_at);
                $totalSeconds += $lastResumed->diffInSeconds(Carbon::now());
            } catch (\Exception $e) {
                Log::error('UserTaskControl: Erro ao parsear last_resumed_at para cálculo de tempo', [
                    'taskId' => $this->currentTask->uuid,
                    'last_resumed_at' => $this->currentTask->last_resumed_at,
                    'error' => $e->getMessage()
                ]);
            }
        }
        return $totalSeconds > 0 ? CarbonInterval::seconds($totalSeconds)->cascade()->forHumans(['short' => true]) : '0s';
    }

    protected function resetTaskDetails(): void
    {
        $this->currentTask = null;
        $this->productName = null;
        $this->stepName = null;
        $this->orderNumber = null;
        $this->quantityPlanned = 0;
        $this->quantityProduced = 0;
        $this->quantityRemaining = 0;
        $this->isPaused = false;
        $this->resetModalFields();
    }

    public function resetModalFields(): void
    {
        $this->selectedPauseReasonUuid = null;
        $this->pauseQuantityProduced = null;
        $this->pauseNotes = '';
        $this->finishQuantityProduced = null;
        $this->finishNotes = '';
        $this->resetValidation();
    }

    public function handleQrCodeScan(array $eventData): void
    {
        $scannedData = $eventData['detail']['decodedText'] ?? null;
        if (!$scannedData) {
            Log::warning('UserTaskControl: Evento qr-code-scanned recebido sem dados válidos.');
            Notification::make()->danger()->title('Erro de Leitura')->body('Não foi possível obter os dados do QR Code.')->send();
            return;
        }
        $this->processScanResult($scannedData);
    }

    public function debugQrCode()
    {
        $qrCode = $this->debugScannedQrCode;
        $qrCode = \Illuminate\Support\Facades\Crypt::decryptString($qrCode);
        $this->processScanResult($qrCode);
    }

    public function processScanResult(string $scannedData): void
    {
        Log::info('UserTaskControl: QR Code Data Recebido para Processamento:', ['data' => $scannedData]);
        //DB::beginTransaction();
        try {
            $uuidPattern = '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}';
            if (!preg_match("/^({$uuidPattern}):({$uuidPattern})$/", $scannedData, $matches)) {
                Log::warning('UserTaskControl: Formato do QR Code não corresponde ao padrão UUID:UUID.', ['data' => $scannedData]);
                throw ValidationException::withMessages(['scan' => 'Formato do QR Code inválido. Esperado UUID:UUID.']);
            }
            $orderItemUuid = $matches[1];
            $stepUuid = $matches[2];

            if (!Str::isUuid($orderItemUuid) || !Str::isUuid($stepUuid)) {
                throw ValidationException::withMessages(['scan' => 'Dados do QR Code contêm UUIDs inválidos.']);
            }

            $orderItem = ProductionOrderItem::where('uuid', $orderItemUuid)->first();
            $step = ProductionStep::where('uuid', $stepUuid)->first();

            if (!$orderItem || !$step || !$orderItem->productionSteps->contains(function (ProductionStep $st) use ($step) { return $st->uuid == $step->uuid; })) {
                Log::error('UserTaskControl: Combinação Item/Etapa não encontrada ou inválida.', [
                    'orderItemUuid' => $orderItemUuid, 'stepUuid' => $stepUuid,
                    'orderItemFound' => !is_null($orderItem), 'stepFound' => !is_null($step),
                    'match' => $orderItem?->production_step_uuid === $step?->uuid
                ]);
                throw ValidationException::withMessages(['scan' => 'Item da Ordem ou Etapa não encontrada, ou não correspondem.']);
            }

            $userId = Auth::id();
            $existingTask = UserCurrentTask::where('user_uuid', $userId)
                ->whereIn('status', ['active', 'paused'])
                ->first();
            if ($existingTask) {
                throw ValidationException::withMessages(['scan' => 'Você já possui uma tarefa ativa ou pausada. Finalize-a antes de iniciar outra.']);
            }

            $newTask = UserCurrentTask::create([
                'user_uuid' => $userId,
                'production_order_item_uuid' => $orderItem->uuid,
                'production_step_uuid' => $step->uuid,
                'started_at' => now(),
                'last_resumed_at' => now(),
                'status' => 'active',
                'total_active_seconds' => 0,
            ]);

            //DB::commit();
            Log::info('UserTaskControl: Nova tarefa iniciada com sucesso.', ['taskId' => $newTask->uuid]);
            $this->loadCurrentTask();
            Notification::make()->success()->title('Tarefa Iniciada!')->body('Você iniciou a tarefa ' . $this->orderNumber . ' - ' . $this->stepName)->send();
            $this->dispatch('scan-success');
            $this->dispatch('close-pause-modal');
        } catch (ValidationException $e) {
            //DB::rollBack();
            Log::error('UserTaskControl: Erro de validação ao processar QR Code:', ['error' => $e->getMessage(), 'errors_detail' => $e->errors()]);
            Notification::make()->danger()->title('Erro no QR Code')->body($e->getMessage())->send();
            $this->dispatch('scan-error', message: $e->getMessage());
        } catch (\Exception $e) {
            //DB::rollBack();
            Log::error('UserTaskControl: Erro inesperado ao processar QR Code:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            Notification::make()->danger()->title('Erro Inesperado')->body('Ocorreu um erro ao iniciar a tarefa. Tente novamente.')->send();
            $this->dispatch('scan-error', message: 'Erro inesperado no servidor.');
        }
    }

    public function pauseTask(): void
    {
        Log::info('UserTaskControl: pauseTask() chamado.');

        if (!$this->currentTask || $this->currentTask->status !== 'active') {
            Log::warning('UserTaskControl: pauseTask() - Ação Inválida.', [
                'hasCurrentTask' => !is_null($this->currentTask),
                'currentTaskStatus' => $this->currentTask?->status
            ]);
            Notification::make()->warning()->title('Ação Inválida')->body('Não há tarefa ativa para pausar.')->send();
            return;
        }

        Log::info('UserTaskControl: pauseTask() - Motivo selecionado (antes da validação):', [
            'selectedPauseReasonUuid' => $this->selectedPauseReasonUuid
        ]);

        try {
            $this->validateOnly('selectedPauseReasonUuid', [
                'selectedPauseReasonUuid' => 'required|uuid|exists:pause_reasons,uuid',
            ], [
                'selectedPauseReasonUuid.required' => 'O motivo da pausa é obrigatório.',
                'selectedPauseReasonUuid.exists' => 'O motivo da pausa selecionado é inválido.',
            ]);
            Log::info('UserTaskControl: pauseTask() - Validação do motivo da pausa OK.');
        } catch (ValidationException $e) {
            Log::error('UserTaskControl: pauseTask() - Falha na validação do motivo da pausa.', [
                'errors' => $e->errors()
            ]);
            return;
        }

        $quantityProducedSession = $this->pauseQuantityProduced ?? 0;
        Log::info('UserTaskControl: pauseTask() - Quantidade produzida na sessão:', [
            'pauseQuantityProduced' => $this->pauseQuantityProduced,
            'quantityProducedSession' => $quantityProducedSession
        ]);

        if (!is_numeric($quantityProducedSession) || $quantityProducedSession < 0) {
            Log::error('UserTaskControl: pauseTask() - Quantidade produzida na sessão inválida.', [
                'quantityProducedSession' => $quantityProducedSession
            ]);
            Notification::make()->danger()->title('Erro na Pausa')->body('Quantidade produzida na sessão inválida.')->send();
            return;
        }

        DB::beginTransaction();
        Log::info('UserTaskControl: pauseTask() - Transação iniciada.');
        try {
            $now = now();
            $durationSeconds = 0;
            if ($this->currentTask->last_resumed_at) {
                try {
                    $lastResumed = Carbon::parse($this->currentTask->last_resumed_at);
                    $durationSeconds = abs($now->diffInSeconds($lastResumed, false));
                    Log::info('UserTaskControl: pauseTask() - Duração da sessão ativa calculada:', [
                        'last_resumed_at' => $this->currentTask->last_resumed_at->toDateTimeString(),
                        'now' => $now->toDateTimeString(),
                        'durationSeconds' => $durationSeconds
                    ]);
                } catch (\Exception $e) {
                    Log::error('UserTaskControl: pauseTask() - Erro ao calcular duração da sessão.', [
                        'taskId' => $this->currentTask->uuid,
                        'last_resumed_at_raw' => $this->currentTask->last_resumed_at,
                        'error' => $e->getMessage()
                    ]);
                    throw new \RuntimeException("Falha ao calcular duração da sessão: " . $e->getMessage(), 0, $e);
                }
            } else {
                Log::info('UserTaskControl: pauseTask() - Não há last_resumed_at, duração da sessão ativa é 0 (primeira pausa).');
            }

            $existingTotalSeconds = (int) ($this->currentTask->total_active_seconds ?? 0);
            $newTotalActiveSeconds = max(0, (int) ($existingTotalSeconds + $durationSeconds));
            Log::info('UserTaskControl: pauseTask() - Cálculo de segundos ativos:', [
                'existingTotalSeconds' => $existingTotalSeconds,
                'newTotalActiveSeconds' => $newTotalActiveSeconds
            ]);

            $updateDataUserCurrentTask = [
                'status' => 'paused',
                'last_pause_at' => $now,
                'last_pause_reason_uuid' => $this->selectedPauseReasonUuid,
                'total_active_seconds' => $newTotalActiveSeconds,
                'last_resumed_at' => null
            ];
            Log::info('UserTaskControl: pauseTask() - Dados para atualizar UserCurrentTask:', $updateDataUserCurrentTask);
            $this->currentTask->update($updateDataUserCurrentTask);
            Log::info('UserTaskControl: pauseTask() - UserCurrentTask atualizado.');

            $taskPauseLogData = [
                'user_current_task_uuid' => $this->currentTask->uuid,
                'production_order_item_uuid' => $this->currentTask->production_order_item_uuid,
                'pause_reason_uuid' => $this->selectedPauseReasonUuid,
                'user_uuid' => Auth::id(),
                'paused_at' => $now,
                'quantity_produced_during_pause' => $quantityProducedSession > 0 ? $quantityProducedSession : null,
                'notes' => $this->pauseNotes ?: null,
            ];
            Log::info('UserTaskControl: pauseTask() - Dados para criar TaskPauseLog:', $taskPauseLogData);
            TaskPauseLog::create($taskPauseLogData);
            Log::info('UserTaskControl: pauseTask() - TaskPauseLog criado.');

            $currentItemTotalQuantity = $this->currentTask->productionOrderItem->quantity_produced ?? 0;
            $newTotalQuantityForItem = $currentItemTotalQuantity + $quantityProducedSession;
            Log::info('UserTaskControl: pauseTask() - Dados para atualizar ProductionOrderItem:', [
                'orderItemId' => $this->currentTask->production_order_item_uuid,
                'currentItemTotalQuantity' => $currentItemTotalQuantity,
                'quantityProducedSession' => $quantityProducedSession,
                'newTotalQuantityForItem' => $newTotalQuantityForItem
            ]);
            $this->currentTask->productionOrderItem()->update(['quantity_produced' => $newTotalQuantityForItem]);
            Log::info('UserTaskControl: pauseTask() - ProductionOrderItem atualizado.');

            DB::commit();
            Log::info('UserTaskControl: pauseTask() - Transação commitada. Tarefa pausada com sucesso.', [
                'taskId' => $this->currentTask->uuid,
                'reason_uuid' => $this->selectedPauseReasonUuid
            ]);

            $this->loadCurrentTask();
            $this->resetModalFields();
            $this->dispatch('refresh');
            $pauseReasonName = PauseReason::find($this->selectedPauseReasonUuid)?->name ?? 'Motivo desconhecido';
            Notification::make()->info()->title('Tarefa Pausada')->body('Motivo: ' . $pauseReasonName)->send();
            $this->dispatch('close-pause-modal');

        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('UserTaskControl: pauseTask() - ValidationException durante a transação.', [
                'errors' => $e->errors()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('UserTaskControl: pauseTask() - Exceção geral durante a transação.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            Notification::make()->danger()->title('Erro ao Pausar')->body('Não foi possível pausar a tarefa. Detalhes: ' . $e->getMessage())->send();
        }
    }

    public function resumeTask(): void
    {
        Log::info('UserTaskControl: resumeTask() chamado.');
        if (!$this->currentTask || $this->currentTask->status !== 'paused') {
            Log::warning('UserTaskControl: resumeTask() - Ação Inválida.', [
                'hasCurrentTask' => !is_null($this->currentTask),
                'currentTaskStatus' => $this->currentTask?->status
            ]);
            Notification::make()->warning()->title('Ação Inválida')->body('Não há tarefa pausada para retomar.')->send();
            return;
        }
        DB::beginTransaction();
        Log::info('UserTaskControl: resumeTask() - Transação iniciada.');
        try {
            $now = now();
            $this->currentTask->update([
                'status' => 'active',
                'last_resumed_at' => $now,
                'last_pause_at' => null,
            ]);
            Log::info('UserTaskControl: resumeTask() - UserCurrentTask atualizado para active.');

            $lastPauseLog = TaskPauseLog::where('user_current_task_uuid', $this->currentTask->uuid)
                ->whereNull('resumed_at')
                ->orderBy('paused_at', 'desc')
                ->first();

            if ($lastPauseLog) {
                Log::info('UserTaskControl: resumeTask() - Encontrado TaskPauseLog para atualizar.', ['logId' => $lastPauseLog->uuid]);
                $lastPauseLog->update([
                    'resumed_at' => $now,
                ]);
                Log::info('UserTaskControl: resumeTask() - TaskPauseLog atualizado.');
            } else {
                Log::warning('UserTaskControl: resumeTask() - Nenhum TaskPauseLog aberto encontrado para retomar.', ['user_current_task_uuid' => $this->currentTask->uuid]);
            }

            DB::commit();
            Log::info('UserTaskControl: resumeTask() - Transação commitada. Tarefa retomada.', ['taskId' => $this->currentTask->uuid]);
            $this->loadCurrentTask();
            $this->dispatch('refresh');
            Notification::make()->success()->title('Tarefa Retomada!')->send();
            $this->dispatch('close-pause-modal');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('UserTaskControl: resumeTask() - Erro ao retomar tarefa:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            Notification::make()->danger()->title('Erro ao Retomar')->body('Não foi possível retomar a tarefa. Detalhes: ' . $e->getMessage())->send();
        }
    }

    public function finishTask(): void
    {
        Log::info('UserTaskControl: finishTask() chamado.');
        if (!$this->currentTask || !in_array($this->currentTask->status, ['active', 'paused'])) {
            Log::warning('UserTaskControl: finishTask() - Ação Inválida.', [
                'hasCurrentTask' => !is_null($this->currentTask),
                'currentTaskStatus' => $this->currentTask?->status
            ]);
            Notification::make()->warning()->title('Ação Inválida')->body('Não há tarefa ativa ou pausada para finalizar.')->send();
            return;
        }

        $quantityProducedSession = $this->finishQuantityProduced ?? 0;
        Log::info('UserTaskControl: finishTask() - Quantidade produzida na sessão final:', [
            'finishQuantityProduced' => $this->finishQuantityProduced,
            'quantityProducedSession' => $quantityProducedSession
        ]);

        if (!is_numeric($quantityProducedSession) || $quantityProducedSession < 0) {
            Log::error('UserTaskControl: finishTask() - Quantidade produzida na sessão final inválida.', [
                'quantityProducedSession' => $quantityProducedSession
            ]);
            Notification::make()->danger()->title('Erro na Finalização')->body('Quantidade produzida na sessão inválida.')->send();
            $this->dispatch('close-pause-modal');
            return;
        }

        DB::beginTransaction();
        Log::info('UserTaskControl: finishTask() - Transação iniciada.');
        try {
            $now = now();
            $taskToFinish = $this->currentTask; // Store reference before it's potentially reset
            $originalStatus = $taskToFinish->status; // Get original status for logic below

            $newTotalActiveSeconds = (int) ($taskToFinish->total_active_seconds ?? 0);

            if ($originalStatus === 'active' && $taskToFinish->last_resumed_at) {
                try {
                    $lastResumed = Carbon::parse($taskToFinish->last_resumed_at);
                    $durationSeconds = abs($now->diffInSeconds($lastResumed, false));
                    $newTotalActiveSeconds += $durationSeconds;
                    Log::info('UserTaskControl: finishTask() - Duração da sessão ativa final calculada:', [
                        'last_resumed_at' => $taskToFinish->last_resumed_at->toDateTimeString(),
                        'now' => $now->toDateTimeString(),
                        'durationSeconds' => $durationSeconds,
                        'newTotalActiveSeconds' => $newTotalActiveSeconds
                    ]);
                } catch (\Exception $e) {
                    Log::error('UserTaskControl: finishTask() - Erro ao calcular duração da sessão final.', [
                        'taskId' => $taskToFinish->uuid,
                        'last_resumed_at_raw' => $taskToFinish->last_resumed_at,
                        'error' => $e->getMessage()
                    ]);
                    throw new \RuntimeException("Falha ao calcular duração da sessão final: " . $e->getMessage(), 0, $e);
                }
            }
            $newTotalActiveSeconds = max(0, (int) $newTotalActiveSeconds);

            // Se a tarefa estava pausada ao finalizar, precisamos fechar o último TaskPauseLog
            if ($originalStatus === 'paused') {
                Log::info('UserTaskControl: finishTask() - Tarefa estava pausada, fechando último TaskPauseLog.');
                $lastPauseLog = TaskPauseLog::where('user_current_task_uuid', $taskToFinish->uuid)
                    ->whereNull('resumed_at')
                    ->orderBy('paused_at', 'desc')
                    ->first();

                if ($lastPauseLog) {
                    Log::info('UserTaskControl: finishTask() - Encontrado TaskPauseLog para fechar.', ['logId' => $lastPauseLog->uuid]);
                    $lastPauseLog->update([
                        'resumed_at' => $now,
                        'notes' => ($lastPauseLog->notes ? $lastPauseLog->notes . "\n" : '') . 'Pausa finalizada automaticamente ao concluir a tarefa.',
                        'quantity_produced_during_pause' => $quantityProducedSession > 0 ? $quantityProducedSession : $lastPauseLog->quantity_produced_during_pause,
                    ]);
                    Log::info('UserTaskControl: finishTask() - TaskPauseLog fechado.');
                } else {
                    Log::warning('UserTaskControl: finishTask() - Tarefa estava pausada, mas nenhum TaskPauseLog aberto foi encontrado.');
                }
            }

            // Atualizar ProductionOrderItem
            $currentItemTotalQuantity = $taskToFinish->productionOrderItem->quantity_produced ?? 0;
            $finalTotalQuantityForItem = $currentItemTotalQuantity;

            // Adiciona a quantidade da sessão apenas se a tarefa estava ativa antes de finalizar.
            // Se estava pausada, a $quantityProducedSession já foi (ou deveria ter sido)
            // registrada no TaskPauseLog ou no modal de pausa.
            if ($originalStatus === 'active') {
                $finalTotalQuantityForItem += $quantityProducedSession;
            }
            // Se estava pausada, a $quantityProducedSession do modal de finalizar
            // pode ser considerada como produzida *após* a última pausa e antes de finalizar.
            // A lógica atual do TaskPauseLog no 'paused' status já tenta capturar isso.
            // Se o modal de 'finish' é para uma quantidade *adicional* mesmo se estava pausado,
            // a lógica de `quantity_produced_during_pause` no `TaskPauseLog` precisa ser clara.
            // Para simplificar e evitar contagem dupla, vamos assumir que $quantityProducedSession
            // do modal de finalizar se refere à produção da última sessão ativa, ou se
            // o usuário trabalhou algo e finalizou direto de um estado pausado (informando no modal de finalizar).
            // A lógica atual do TaskPauseLog ao fechar no finishTask já usa $quantityProducedSession.

            Log::info('UserTaskControl: finishTask() - Dados para atualizar ProductionOrderItem (final):', [
                'orderItemId' => $taskToFinish->productionOrderItem->uuid,
                'currentItemTotalQuantity' => $currentItemTotalQuantity,
                'quantityProducedSessionFromFinishModal' => $quantityProducedSession,
                'originalStatus' => $originalStatus,
                'finalTotalQuantityForItem' => $finalTotalQuantityForItem
            ]);

            $taskToFinish->productionOrderItem()->update([
                'quantity_produced' => $finalTotalQuantityForItem
            ]);
            Log::info('UserTaskControl: finishTask() - ProductionOrderItem atualizado (final).');

            // Deletar o UserCurrentTask
            $taskIdToDelete = $taskToFinish->uuid;
            $taskToFinish->delete();
            Log::info('UserTaskControl: finishTask() - UserCurrentTask record deleted.', ['taskId' => $taskIdToDelete]);

            DB::commit();
            Log::info('UserTaskControl: finishTask() - Transação commitada. Tarefa finalizada com sucesso.', ['taskId' => $taskIdToDelete]);

            $this->resetTaskDetails(); // Isso vai limpar $this->currentTask
            $this->dispatch('refresh');
            Notification::make()->success()->title('Tarefa Finalizada!')->body('A tarefa foi concluída com sucesso.')->send();
            $this->dispatch('close-pause-modal'); // Garante que qualquer modal seja fechado
            $this->dispatch('close-finish-modal'); // Se você tiver um evento específico para o modal de finalizar

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('UserTaskControl: finishTask() - Erro ao finalizar tarefa:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            Notification::make()->danger()->title('Erro ao Finalizar')->body('Não foi possível finalizar a tarefa. Detalhes: ' . $e->getMessage())->send();
        }
    }

    public function render(): View
    {
        return view('livewire.user-task-control');
    }
}
