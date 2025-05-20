<?php

namespace App\Livewire;

use App\Models\TransportOrder;
use App\Models\TransportOrderItem;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class DriverDeliveryManager extends Component
{
    use WithFileUploads;

    public ?User $driver;
    public $approvedOrders = [];
    public ?TransportOrder $inProgressOrder = null;
    public ?TransportOrderItem $nextDeliveryItem = null;
    public ?TransportOrderItem $currentItemForProcessing = null; // Item sendo processado (scan/fotos)

    public string $scannedQrCodeData = '';
    public $uploadedPhotos = []; // Para o upload de múltiplos arquivos
    public string $returnReason = '';

    public bool $showQrScanModal = false;
    public bool $showPhotoUploadModal = false;
    public bool $showConfirmationModal = false;
    public bool $showRejectionReasonModal = false;

    public ?string $expectedItemUuid = null;
    public ?TransportOrderItem $itemActuallyScanned = null;
    public bool $showChangeDeliveryConfirmationModal = false;

    public function mount()
    {
        $this->driver = Auth::user();
        $this->loadDriverOrders();
    }

    public function loadDriverOrders()
    {
        if (!$this->driver) {
            return;
        }

        $this->approvedOrders = TransportOrder::where('driver_id', $this->driver->uuid)
            ->where('status', TransportOrder::STATUS_APPROVED)
            ->orderBy('planned_departure_datetime', 'asc')
            ->get();

        $this->inProgressOrder = TransportOrder::where('driver_id', $this->driver->uuid)
            ->where('status', TransportOrder::STATUS_IN_PROGRESS)
            ->with(['items' => function ($query) {
                $query->orderBy('delivery_sequence', 'asc');
            }, 'items.client', 'items.product'])
            ->first();

        if ($this->inProgressOrder) {
            $this->findNextDeliveryItem();
        } else {
            $this->nextDeliveryItem = null;
        }
        $this->resetProcessingState();
    }

    protected function findNextDeliveryItem()
    {
        if (!$this->inProgressOrder) {
            $this->nextDeliveryItem = null;
            return;
        }

        $this->nextDeliveryItem = $this->inProgressOrder->items
            ->whereNotIn('status', [TransportOrderItem::STATUS_COMPLETED, TransportOrderItem::STATUS_RETURNED])
            ->sortBy('delivery_sequence')
            ->first();
    }

    public function startOrder(string $orderUuid)
    {
        $order = TransportOrder::where('uuid', $orderUuid)
            ->where('driver_id', $this->driver->uuid)
            ->where('status', TransportOrder::STATUS_APPROVED)
            ->first();

        if ($order) {
            $order->status = TransportOrder::STATUS_IN_PROGRESS;
            $order->actual_departure_datetime = now();
            $order->save();
            $this->loadDriverOrders();
            Notification::make()->title('Ordem iniciada!')->success()->send();
        } else {
            Notification::make()->title('Erro ao iniciar ordem.')->danger()->send();
        }
    }

    public function openQrScanModal(?string $itemUuid = null)
    {
        $this->expectedItemUuid = $itemUuid;
        $this->reset(['scannedQrCodeData', 'currentItemForProcessing', 'itemActuallyScanned', 'showChangeDeliveryConfirmationModal']);
        $this->uploadedPhotos = [];
        $this->showQrScanModal = true;
        $this->dispatch('openDriverDeliveryScanner');
    }

    public function handleCameraError()
    {
        $this->showQrScanModal = false;
        Notification::make()
            ->title('Erro de Câmera')
            ->body('Não foi possível acessar a câmera. Verifique as permissões e tente novamente.')
            ->danger()
            ->send();
    }

    public function processQrCodeScan()
    {
        $this->validate([
            'scannedQrCodeData' => 'required|uuid',
        ]);

        $this->itemActuallyScanned = TransportOrderItem::with(['client', 'product', 'transportOrder'])
            ->where('uuid', $this->scannedQrCodeData)
            ->first();

        if (!$this->itemActuallyScanned || $this->itemActuallyScanned->transportOrder->uuid !== $this->inProgressOrder?->uuid) {
            Notification::make()->title('QR Code inválido')->body('Este QR Code não pertence à ordem de transporte atual.')->danger()->send();
            $this->showQrScanModal = false; // Fecha o modal
            $this->dispatch('closeDriverDeliveryScanner'); // Pede para o JS parar o scanner
            $this->reset(['expectedItemUuid', 'itemActuallyScanned', 'scannedQrCodeData']);
            return;
        }

        if (in_array($this->itemActuallyScanned->status, [TransportOrderItem::STATUS_COMPLETED, TransportOrderItem::STATUS_RETURNED])) {
            Notification::make()->title('Item já processado')->warning()->send();
            $this->showQrScanModal = false; // Fecha o modal
            $this->dispatch('closeDriverDeliveryScanner'); // Pede para o JS parar o scanner
            $this->reset(['expectedItemUuid', 'itemActuallyScanned', 'scannedQrCodeData']);
            return;
        }

        if ($this->expectedItemUuid) {
            if ($this->itemActuallyScanned->uuid === $this->expectedItemUuid) {
                $this->currentItemForProcessing = $this->itemActuallyScanned;
                $this->showQrScanModal = false;
                $this->dispatch('closeDriverDeliveryScanner'); // Pede para o JS parar o scanner
                $this->openPhotoUploadModal();
            } else {
                $this->showQrScanModal = false;
                $this->dispatch('closeDriverDeliveryScanner'); // Pede para o JS parar o scanner
                $this->showChangeDeliveryConfirmationModal = true;
            }
        } else {
            $this->currentItemForProcessing = $this->itemActuallyScanned;
            $this->nextDeliveryItem = $this->itemActuallyScanned;
            $this->showQrScanModal = false;
            $this->dispatch('closeDriverDeliveryScanner'); // Pede para o JS parar o scanner
            $this->openPhotoUploadModal();
        }
    }

    public function confirmChangeDeliveryAndProceed(bool $confirm)
    {
        $this->showChangeDeliveryConfirmationModal = false;

        if ($confirm && $this->itemActuallyScanned) {
            $this->currentItemForProcessing = $this->itemActuallyScanned;
            $this->nextDeliveryItem = $this->itemActuallyScanned;
            $this->openPhotoUploadModal();
        } else {
            Notification::make()->title('Operação cancelada.')->body('A próxima entrega continua sendo para o cliente ' . $this->nextDeliveryItem?->client?->name . '.')->info()->send();
            $this->reset(['itemActuallyScanned', 'expectedItemUuid']);
        }
    }

    public function openPhotoUploadModal()
    {
        if (!$this->currentItemForProcessing) {
            Notification::make()->title('Erro')->body('Nenhum item selecionado para processamento.')->danger()->send();
            $this->resetProcessingState();
            return;
        }
        $this->showQrScanModal = false;
        $this->dispatch('closeDriverDeliveryScanner');
        $this->uploadedPhotos = [];
        $this->showPhotoUploadModal = true;
    }

    public function savePhotosAndProceed()
    {
        $this->validate([
            'uploadedPhotos.*' => 'image|max:5120',
            'uploadedPhotos' => 'nullable|array|max:5',
        ]);

        if (!$this->currentItemForProcessing) {
            Notification::make()->title('Nenhum item selecionado para upload de fotos.')->danger()->send();
            return;
        }

        $photoPaths = $this->currentItemForProcessing->delivery_photos ?? [];
        if ($this->uploadedPhotos) {
            foreach ($this->uploadedPhotos as $photo) {
                $path = $photo->store('transport_order_items/' . $this->currentItemForProcessing->uuid . '/photos', 'public');
                $photoPaths[] = $path;
            }
            $this->currentItemForProcessing->delivery_photos = $photoPaths;
            $this->currentItemForProcessing->save();
            Notification::make()->title('Fotos salvas!')->success()->send();
        } else {
            Notification::make()->title('Nenhuma foto nova enviada.')->info()->send();
        }
        $this->showPhotoUploadModal = false;
        $this->showConfirmationModal = true;
    }

    public function confirmDelivery(bool $accepted)
    {
        if (!$this->currentItemForProcessing) return;

        $this->showConfirmationModal = false;
        if ($accepted) {
            $this->currentItemForProcessing->status = TransportOrderItem::STATUS_COMPLETED;
            $this->currentItemForProcessing->delivered_at = now();
            $this->currentItemForProcessing->processed_by_user_id = $this->driver->uuid;
            $this->currentItemForProcessing->save();
            Notification::make()->title('Entrega confirmada como CONCLUÍDA.')->success()->send();
        } else {
            $this->returnReason = '';
            $this->showRejectionReasonModal = true;
            return;
        }
        $this->checkOrderStatusAndComplete();
        $this->loadDriverOrders();
        $this->resetProcessingState();
    }

    public function submitRejection()
    {
        if (!$this->currentItemForProcessing) return;

        $this->validate(['returnReason' => 'required|string|min:10']);

        $this->currentItemForProcessing->status = TransportOrderItem::STATUS_RETURNED;
        $this->currentItemForProcessing->returned_at = now();
        $this->currentItemForProcessing->return_reason = $this->returnReason;
        $this->currentItemForProcessing->processed_by_user_id = $this->driver->uuid;
        $this->currentItemForProcessing->save();

        Notification::make()->title('Entrega registrada como DEVOLVIDA.')->warning()->send();
        $this->showRejectionReasonModal = false;
        $this->checkOrderStatusAndComplete(); // Verifica se a ordem toda foi concluída
        $this->loadDriverOrders(); // Recarrega para atualizar o próximo item e a lista de ordens
        $this->resetProcessingState(); // Limpa o estado do item atual
    }

    protected function checkOrderStatusAndComplete()
    {
        if ($this->inProgressOrder) {
            // Recarrega a ordem com os itens para garantir dados atualizados
            $this->inProgressOrder->load('items');
            $pendingItems = $this->inProgressOrder->items()
                ->whereNotIn('status', [TransportOrderItem::STATUS_COMPLETED, TransportOrderItem::STATUS_RETURNED])
                ->count();

            if ($pendingItems === 0) {
                $this->inProgressOrder->status = TransportOrder::STATUS_COMPLETED;
                $this->inProgressOrder->actual_arrival_datetime = now();
                $this->inProgressOrder->save();
                Notification::make()->title('Todos os itens processados. Ordem de transporte concluída!')->success()->send();
                // $this->inProgressOrder = null; // Limpa a ordem em progresso da UI
                // $this->nextDeliveryItem = null;
            }
        }
    }

    protected function resetProcessingState()
    {
        $this->reset([
            'scannedQrCodeData',
            'currentItemForProcessing',
            'uploadedPhotos',
            'returnReason',
            'showQrScanModal',
            'showPhotoUploadModal',
            'showConfirmationModal',
            'showRejectionReasonModal',
            'expectedItemUuid', // Adicionado
            'itemActuallyScanned', // Adicionado
            'showChangeDeliveryConfirmationModal' // Adicionado
        ]);
    }

    public function render()
    {
        return view('livewire.driver-delivery-manager');
    }
}
