<?php

namespace App\Filament\Resources\TransportOrderResource\Pages;

use App\Filament\Resources\TransportOrderResource;
use App\Models\TransportOrder; // Importe o modelo
use Filament\Actions;
use Filament\Notifications\Notification; // Para notificações (opcional)
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTransportOrder extends EditRecord
{
    protected static string $resource = TransportOrderResource::class;

    protected function getFormActions(): array
    {
        // Oculta a ação de salvar se a ordem estiver concluída
        return [
            $this->getSaveFormAction()
                ->visible(fn (?Model $record): bool => $record instanceof TransportOrder && $record->status !== TransportOrder::STATUS_COMPLETED),
            $this->getCancelFormAction(),
        ];
    }

    // Opcional: Redirecionar ou mostrar notificação se tentar acessar a edição de uma ordem concluída.
    // Isso pode ser feito no `mount()` ou `beforeFill()`
    protected function beforeFill(): void
    {
        if ($this->record instanceof TransportOrder && $this->record->status === TransportOrder::STATUS_COMPLETED) {
            Notification::make()
                ->title('Ordem Concluída')
                ->body('Ordens de transporte concluídas não podem ser editadas.')
                ->warning()
                ->send();

            // Descomente a linha abaixo se quiser redirecionar o usuário
            // $this->redirect(static::getResource()::getUrl('index'));
        }
    }
}
