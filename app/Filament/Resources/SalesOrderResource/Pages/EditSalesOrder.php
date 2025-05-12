<?php

namespace App\Filament\Resources\SalesOrderResource\Pages;

use App\Filament\Resources\SalesOrderResource;
use App\Models\SalesOrder; // Importar o modelo
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification; // Para notificações, se necessário diretamente aqui
use Illuminate\Validation\ValidationException; // Para capturar erros de transição

class EditSalesOrder extends EditRecord
{
    protected static string $resource = SalesOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generatePdf')
                ->label('Gerar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->url(fn (SalesOrder $record): string => route('sales-orders.pdf', $record->uuid))
                ->openUrlInNewTab(),

            Actions\Action::make('approveOrder')
                ->label('Aprovar Pedido')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Aprovar Pedido de Venda')
                ->modalDescription('Tem certeza que deseja aprovar este pedido? Uma Ordem de Produção será gerada.')
                ->visible(function (SalesOrder $record): bool {
                    return $record->status === SalesOrder::STATUS_PENDING && $record->items()->exists();
                })
                ->action(function (SalesOrder $record): void {
                    try {
                        $record->status = SalesOrder::STATUS_APPROVED;
                        $record->save(); // O hook 'updating' no modelo SalesOrder cuidará da criação da OP

                        Notification::make()
                            ->title('Pedido Aprovado')
                            ->body("O pedido {$record->order_number} foi aprovado e uma Ordem de Produção foi gerada.")
                            ->success()
                            ->send();

                        $this->dispatch('refresh');
                    } catch (ValidationException $e) {
                        $errorMessages = [];
                        foreach ($e->errors() as $fieldErrors) {
                            $errorMessages = array_merge($errorMessages, $fieldErrors);
                        }
                        $errorMessage = !empty($errorMessages) ? implode(' ', $errorMessages) : 'Não foi possível aprovar o pedido devido a regras de negócio ou dados inválidos.';
                        Notification::make()
                            ->title('Erro ao Aprovar Pedido')
                            ->danger()
                            ->body($errorMessage)
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erro ao Aprovar Pedido')
                            ->danger()
                            ->body('Ocorreu um erro inesperado: ' . $e->getMessage())
                            ->send();
                        \Illuminate\Support\Facades\Log::error('Erro ao aprovar pedido (EditPage): ' . $e->getMessage(), ['exception' => $e, 'sales_order_uuid' => $record->uuid]);
                    }
                }),

            // Ação de Cancelar Pedido
            Actions\Action::make('cancelOrder')
                ->label('Cancelar Pedido')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Cancelar Pedido de Venda')
                ->modalDescription('Informe o motivo e detalhes do cancelamento. Esta ação não pode ser desfeita facilmente.')
                ->visible(fn (SalesOrder $record): bool => $record->status !== SalesOrder::STATUS_CANCELLED)
                ->form([
                    \Filament\Forms\Components\TextInput::make('cancellation_reason')
                        ->label('Motivo do Cancelamento')
                        ->required()
                        ->maxLength(255),
                    \Filament\Forms\Components\Textarea::make('cancellation_details')
                        ->label('Detalhes Adicionais (Opcional)')
                        ->rows(3),
                ])
                ->action(function (SalesOrder $record, array $data): void {
                    try {
                        $record->cancellation_reason = $data['cancellation_reason'] ?? 'Motivo não informado';
                        $record->cancellation_details = $data['cancellation_details'] ?? null;
                        $record->cancelled_at = now();
                        $record->status = SalesOrder::STATUS_CANCELLED;
                        $record->save();

                        Notification::make()
                            ->title('Pedido Cancelado')
                            ->body("O pedido {$record->order_number} foi cancelado.")
                            ->success()
                            ->send();

                        $this->dispatch('refresh');

                    } catch (ValidationException $e) {
                        $errorMessages = [];
                        foreach ($e->errors() as $fieldErrors) {
                            $errorMessages = array_merge($errorMessages, $fieldErrors);
                        }
                        $errorMessage = !empty($errorMessages) ? implode(' ', $errorMessages) : 'Não foi possível cancelar o pedido devido a regras de negócio ou dados inválidos.';

                        Notification::make()
                            ->title('Erro ao Cancelar Pedido')
                            ->danger()
                            ->body($errorMessage)
                            ->send();
                    } catch (\Exception $e) {
                        $errorMessage = 'Ocorreu um erro inesperado.';
                        if (app()->environment('local', 'development')) {
                            $errorMessage .= ' Detalhes: ' . $e->getMessage();
                        }
                        \Illuminate\Support\Facades\Log::error('Erro ao cancelar pedido (EditPage): ' . $e->getMessage(), ['exception' => $e, 'data' => $data, 'sql' => method_exists($e, 'getSql') ? $e->getSql() : 'N/A']);
                        Notification::make()
                            ->title('Erro ao Cancelar Pedido')
                            ->danger()
                            ->body($errorMessage)
                            ->send();
                    }
                }),
        ];
    }
}
