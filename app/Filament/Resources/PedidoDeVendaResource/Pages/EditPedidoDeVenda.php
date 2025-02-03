<?php

namespace App\Filament\Resources\PedidoResource\Pages;

use App\Filament\Resources\PedidoDeVendaResource;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\EditRecord;

class EditPedidoDeVenda extends EditRecord
{
    protected static string $resource = PedidoDeVendaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('Cancelar Pedido')
                ->hidden(fn($record) => $record->status === 'cancelado')
                ->color('warning')
                ->requiresConfirmation()
                ->form([
                    Textarea::make('justificativa')
                ])->action(function ($data) {
                    $this->record->status = 'cancelado';
                    $this->record->justificativa = $data['justificativa'];
                    $this->record->save();
                })
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        dd($data);
        $data['quantidade'] = intval($data['quantidade']);
        $data['desconto'] = floatval($data['desconto']);
        $data['produtos'] = json_encode($data['produtos']);
        return parent::mutateFormDataBeforeSave($data);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['produtos'] = json_decode($data['produtos'], true);
        return parent::mutateFormDataBeforeFill($data);
    }
}
