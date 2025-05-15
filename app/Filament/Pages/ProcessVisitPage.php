<?php

namespace App\Filament\Pages;

use App\Filament\Resources\SalesOrderResource;
use App\Filament\Resources\SalesVisitResource;
use App\Models\Client;
use App\Models\SalesOrder;
use App\Models\SalesVisit;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProcessVisitPage extends Page implements HasForms
{
    use InteractsWithForms;

    public int $actionsReRenderKey = 0;

    protected static ?string $navigationIcon = 'heroicon-o-cursor-arrow-rays';
    protected static string $view = 'filament.pages.process-visit-page';
    protected static ?string $title = 'Processar Visita de Venda';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'processar-visita';

    public ?SalesVisit $record = null;
    public ?Client $client = null;
    public array $pastOrders = [];

    protected function getListeners(): array
    {
        return [
            'itemsUpdated' => 'handleItemsUpdated',
        ];
    }

    public function handleItemsUpdated(): void
    {
        $this->refreshRecordData(); // Atualiza os dados do registro
        $this->actionsReRenderKey++; // Altera a propriedade dummy para forçar o Livewire a reavaliar
    }

    protected function refreshRecordData(): void
    {
        if ($this->record) {
            $this->record->refresh();
            if ($this->record->sales_order_id) {
                $this->record->load(['salesOrder' => fn($query) => $query->withCount('items')]);
            } else {
                if ($this->record->relationLoaded('salesOrder')) {
                    $this->record->unsetRelation('salesOrder');
                }
            }
        }
    }

    public function mount(Request $request): void
    {
        $visitUuid = $request->query('visit_uuid');

        if (!$visitUuid || !is_string($visitUuid) || strlen($visitUuid) !== 36) {
            Notification::make()->danger()->title('Erro')->body('UUID da visita inválido ou não fornecido.')->send();
            $this->redirect(SalesVisitResource::getUrl('index'));
            return;
        }

        $this->record = SalesVisit::with([
            'client.salesOrders' => function ($query) {
                $query->whereNot('status', SalesOrder::STATUS_DRAFT)
                    ->orderBy('order_date', 'desc')
                    ->limit(10);
            },
            'salesOrder' => function ($query) {
                $query->withCount('items');
            },
            'company',
            'assignedTo'
        ])->find($visitUuid);

        if (!$this->record) {
            Notification::make()->danger()->title('Erro')->body('Visita não encontrada ou inacessível.')->send();
            $this->redirect(SalesVisitResource::getUrl('index'));
            return;
        }

        if ($this->record->assigned_to_user_id !== Auth::id() && !Auth::user()->can('view_all_sales_visits')) {
            Notification::make()->danger()->title('Acesso Negado')->body('Você não tem permissão para processar esta visita.')->send();
            $this->redirect(SalesVisitResource::getUrl('index'));
            return;
        }

        if (in_array($this->record->status, [SalesVisit::STATUS_COMPLETED, SalesVisit::STATUS_CANCELLED, SalesVisit::STATUS_RESCHEDULED])) {
            Notification::make()->warning()->title('Visita Não Processável')
                ->body('Esta visita já foi '.strtolower(SalesVisit::getStatusOptions()[$this->record->status] ?? $this->record->status).' e não pode ser processada novamente.')
                ->send();
            $this->redirect(SalesVisitResource::getUrl('index'));
            return;
        }

        $this->client = $this->record->client;

        if ($this->client && $this->client->salesOrders) {
            $clientOrders = $this->client->salesOrders;
            if ($this->record->sales_order_id) {
                $this->pastOrders = $clientOrders->filter(function ($order) {
                    return $order->uuid !== $this->record->sales_order_id;
                })->values()->toArray();
            } else {
                $this->pastOrders = $clientOrders->toArray();
            }
        } else {
            $this->pastOrders = [];
        }

        $this->form->fill([
            'report_reason_no_order' => $this->record->report_reason_no_order,
            'report_corrective_actions' => $this->record->report_corrective_actions,
        ]);
    }

    protected function getFormModel(): Model|string|null
    {
        return $this->record;
    }
    protected function getReportFormSchema(): array
    {
        return [
            TextInput::make('report_reason_no_order')
                ->label('Motivo da Não Geração de Pedido')
                ->required()
                ->maxLength(255),
            Textarea::make('report_corrective_actions')
                ->label('Ações Corretivas Sugeridas')
                ->rows(5)
                ->required(),
        ];
    }

    public function startVisit(): void
    {
        if (!$this->record) return;

        if ($this->record->status === SalesVisit::STATUS_SCHEDULED) {
            $this->record->status = SalesVisit::STATUS_IN_PROGRESS;
            $this->record->visit_start_time = now();
            if ($this->record->save()) {
                Notification::make()->success()->title('Visita Iniciada!')->send();
                $this->refreshRecordData();
                $this->actionsReRenderKey++;
            } else {
                Notification::make()->danger()->title('Erro')->body('Não foi possível iniciar a visita.')->send();
            }
        } else {
            Notification::make()->warning()->title('Ação Inválida')
                ->body('Esta visita não pode ser iniciada (status atual: ' . (SalesVisit::getStatusOptions()[$this->record->status] ?? $this->record->status) . ').')
                ->send();
        }
    }

    public function createSalesOrderAction(): Action
    {
        return Action::make('createSalesOrder')
            ->label('Lançar Novo Pedido')
            ->icon('heroicon-o-shopping-cart')
            ->color('primary')
            ->modal()
            ->modalHeading('Criar Novo Pedido de Venda')
            ->modalDescription('Informe os detalhes para o novo pedido.')
            ->modalSubmitActionLabel('Criar Pedido')
            ->modalWidth(MaxWidth::Medium)
            ->form([
                DatePicker::make('expected_delivery_date')
                    ->label('Data Prevista para Entrega')
                    ->native(false)
                    ->required()
                    ->minDate(now()->addDay()),
                Textarea::make('order_notes')
                    ->label('Observações do Pedido')
                    ->rows(3)
                    ->maxLength(1000),
            ])
            ->action(function (array $data): void {
                if (!$this->record || !$this->client) {
                    Notification::make()->danger()->title('Erro')->body('Não foi possível identificar a visita ou o cliente.')->send();
                    return;
                }

                $orderNumber = 'PED-' . strtoupper(Str::random(4)) . '-' . time(); // TODO: Melhorar geração de número de pedido

                $newSalesOrder = SalesOrder::create([
                    'client_id' => $this->client->uuid,
                    'sales_visit_id' => $this->record->uuid,
                    'company_id' => $this->record->company_id,
                    'user_id' => Auth::id(),
                    'order_number' => $orderNumber,
                    'status' => SalesOrder::STATUS_DRAFT,
                    'delivery_deadline' => Carbon::parse($data['expected_delivery_date']),
                    'notes' => $data['order_notes'],
                    'order_date' => now(),
                ]);

                $this->record->sales_order_id = $newSalesOrder->uuid;
                if ($this->record->save()) {
                    $this->record->load(['salesOrder' => fn($query) => $query->withCount('items')]);
                    $this->refreshRecordData();
                    $this->actionsReRenderKey++;
                    Notification::make()->success()->title('Pedido Criado!')->body("O pedido {$newSalesOrder->order_number} foi criado com sucesso.")->send();
                } else {
                    $newSalesOrder->delete();
                    Notification::make()->danger()->title('Erro Crítico')->body('Não foi possível associar o pedido à visita. O pedido foi cancelado.')->send();
                }
            })
            ->visible(fn(): bool => $this->record && $this->record->status === SalesVisit::STATUS_IN_PROGRESS && !$this->record->sales_order_id);
    }

    public function rescheduleVisitAction(): Action
    {
        return Action::make('rescheduleVisit')
            ->label('Reagendar Visita')
            ->icon('heroicon-o-calendar')
            ->color('warning')
            ->modal()
            ->modalWidth(MaxWidth::Small)
            ->modalHeading('Reagendar Visita')
            ->modalDescription('Informe a nova data/hora e o motivo do reagendamento.')
            ->modalSubmitActionLabel('Confirmar Reagendamento')
            ->form([
                DateTimePicker::make('new_scheduled_at')
                    ->label('Nova Data/Hora Agendada')
                    ->native(false)->seconds(false)->required()
                    ->default(now()->addDay()->setHour(9)->setMinute(0))
                    ->minDate(now()->addMinutes(5)),
                Textarea::make('reschedule_reason')->label('Motivo do Reagendamento')->rows(3)->required()->maxLength(500),
            ])
            ->action(function (array $data): void {
                if (!$this->record) {
                    Notification::make()->danger()->title('Erro')->body('Visita não encontrada.')->send();
                    return;
                }
                $newScheduledAt = Carbon::parse($data['new_scheduled_at']);
                $rescheduleReason = $data['reschedule_reason'];
                $originalNotes = $this->record->notes ?? '';
                $rescheduleNote = "[VISITA REAGENDADA] - Nova data: {$newScheduledAt->format('d/m/Y H:i')} - Motivo: {$rescheduleReason}";

                $this->record->fill(['notes' => $originalNotes . ($originalNotes ? "\n\n" : "") . $rescheduleNote, 'status' => SalesVisit::STATUS_RESCHEDULED]);
                if (!$this->record->save()) {
                    Notification::make()->danger()->title('Erro')->body('Não foi possível atualizar a visita original.')->send();
                    return;
                }

                $newVisit = $this->record->replicate();
                $newVisit->fill([
                    'uuid' => Str::uuid()->toString(), 'scheduled_at' => $newScheduledAt, 'status' => SalesVisit::STATUS_SCHEDULED,
                    'visited_at' => null, 'visit_start_time' => null, 'visit_end_time' => null,
                    'sales_order_id' => null, 'notes' => null, 'cancellation_reason' => null,
                    'cancellation_details' => null, 'report_reason_no_order' => null, 'report_corrective_actions' => null,
                ]);

                if ($newVisit->save()) {
                    Notification::make()->success()->title('Visita Reagendada!')->body('Uma nova visita foi criada com a data informada.')->send();
                    $this->redirect(SalesVisitResource::getUrl('index'));
                } else {
                    Notification::make()->danger()->title('Erro')->body('Não foi possível criar a nova visita reagendada.')->send();
                }
            })
            ->visible(fn(): bool => $this->record && $this->record->status === SalesVisit::STATUS_SCHEDULED);
    }

    public function finalizeVisitWithoutOrderAction(): Action
    {
        return Action::make('finalizeVisitWithoutOrder')
            ->label('Finalizar Visita Sem Pedido')
            ->icon('heroicon-o-document-minus')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Relatório de Visita (Sem Pedido)')
            ->modalDescription('Por favor, preencha o motivo da não geração do pedido e as ações corretivas.')
            ->modalSubmitActionLabel('Salvar Relatório e Finalizar')
            ->form($this->getReportFormSchema()) // Reutiliza o schema do formulário de relatório
            ->modalWidth(MaxWidth::Medium)
            ->action(function (array $data): void {
                if (!$this->record) {
                    Notification::make()->danger()->title('Erro')->body('Registro da visita não encontrado.')->send();
                    return;
                }
                $this->refreshRecordData();

                $this->record->report_reason_no_order = $data['report_reason_no_order'] ?? null;
                $this->record->report_corrective_actions = $data['report_corrective_actions'] ?? null;
                $this->record->status = SalesVisit::STATUS_COMPLETED;
                $this->record->visit_end_time = now();
                if (!$this->record->visited_at) {
                    $this->record->visited_at = $this->record->visit_end_time;
                }

                if (!$this->record->save()) {
                    Notification::make()->danger()->title('Erro ao Finalizar Visita')->body("Não foi possível salvar as alterações na visita.")->send();
                    return;
                }
                Notification::make()->success()->title('Visita Finalizada!')->body('A visita foi concluída sem pedido.')->send();
                $this->redirect(SalesVisitResource::getUrl('index'));
            })
            ->visible(function(): bool {
                return $this->record &&
                    $this->record->status === SalesVisit::STATUS_IN_PROGRESS &&
                    !$this->record->sales_order_id;
            });
    }

    public function finalizeVisitWithEmptyOrderAction(): Action
    {
        return Action::make('finalizeVisitWithEmptyOrder')
            ->label('Finalizar Visita (Pedido Vazio)')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Relatório de Visita (Pedido Vazio)')
            ->modalDescription('O pedido associado está vazio e será removido. Por favor, preencha o motivo da não geração do pedido e as ações corretivas.')
            ->modalSubmitActionLabel('Salvar Relatório, Remover Pedido e Finalizar')
            ->form($this->getReportFormSchema()) // Reutiliza o schema
            ->modalWidth(MaxWidth::Medium)
            ->action(function (array $data): void {
                if (!$this->record) {
                    Notification::make()->danger()->title('Erro')->body('Registro da visita não encontrado.')->send();
                    return;
                }
                $this->refreshRecordData();

                $currentSalesOrder = $this->record->salesOrder;
                if (!$currentSalesOrder || !isset($currentSalesOrder->items_count)) {
                    Notification::make()->danger()->title('Erro Inesperado')->body('Não foi possível verificar os itens do pedido.')->send();
                    return;
                }

                $currentSalesOrder->loadCount('items');

                if ($currentSalesOrder->items_count !== 0) { // Outra salvaguarda
                    Notification::make()->warning()->title('Ação Inválida')->body('O pedido associado não está vazio. Use a opção "Finalizar Visita".')->send();
                    return;
                }

                $this->record->report_reason_no_order = $data['report_reason_no_order'] ?? null;
                $this->record->report_corrective_actions = $data['report_corrective_actions'] ?? null;

                $orderNumber = $currentSalesOrder->order_number;
                $currentSalesOrder->forceDelete(); // Exclui permanentemente o pedido vazio

                $this->record->sales_order_id = null;
                $this->record->unsetRelation('salesOrder'); // Limpa a relação em memória

                $this->record->status = SalesVisit::STATUS_COMPLETED;
                $this->record->visit_end_time = now();
                if (!$this->record->visited_at) {
                    $this->record->visited_at = $this->record->visit_end_time;
                }

                if (!$this->record->save()) {
                    Notification::make()->danger()->title('Erro ao Finalizar Visita')->body("Não foi possível salvar as alterações na visita.")->send();
                    // Considerar reverter a exclusão do pedido se o save da visita falhar?
                    return;
                }
                Notification::make()->success()->title('Visita Finalizada!')->body("A visita foi concluída e o pedido vazio ({$orderNumber}) foi removido.")->send();
                $this->redirect(SalesVisitResource::getUrl('index'));
            })
            ->visible(fn(): bool => $this->record &&
                $this->record->status === SalesVisit::STATUS_IN_PROGRESS &&
                $this->record->salesOrder &&
                isset($this->record->salesOrder->items_count) &&
                $this->record->salesOrder->items_count === 0);
    }

    // Ação 3: Finalizar visita COM pedido de venda e ITENS
    public function finalizeVisitWithOrderAction(): Action
    {
        return Action::make('finalizeVisitWithOrder')
            ->label('Finalizar Visita')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Confirmar Finalização da Visita')
            ->modalDescription('Deseja realmente finalizar esta visita? O pedido associado será movido para "Pendente".')
            ->modalSubmitActionLabel('Finalizar')
            ->modalWidth(MaxWidth::ExtraSmall)
            ->action(function (): void {
                if (!$this->record) {
                    Notification::make()->danger()->title('Erro')->body('Registro da visita não encontrado.')->send();
                    return;
                }

                // 1. Garante que temos os dados mais recentes da visita e do pedido associado
                $this->refreshRecordData();

                $currentSalesOrder = $this->record->salesOrder;

                // 2. Validações
                if (!$currentSalesOrder) {
                    Notification::make()->danger()->title('Erro Inesperado')->body('Pedido associado não encontrado.')->send();
                    return;
                }

                // Garante que items_count está carregado e é o mais recente para esta verificação.
                // refreshRecordData já deve ter feito isso, mas uma verificação extra não prejudica.
                $currentSalesOrder->loadCount('items');

                if ($currentSalesOrder->items_count === 0) {
                    Notification::make()->warning()->title('Ação Inválida')->body('O pedido associado está vazio. Use a opção "Finalizar Visita (Pedido Vazio)".')->send();
                    return;
                }

                // 3. Atualiza o status do SalesOrder
                $currentSalesOrder->status = SalesOrder::STATUS_PENDING;

                // 4. Salva o SalesOrder e VERIFICA o resultado
                if (!$currentSalesOrder->save()) {
                    Notification::make()->danger()->title('Erro ao Atualizar Pedido')->body("Não foi possível atualizar o status do pedido {$currentSalesOrder->order_number} para Pendente.")->send();
                    return; // Interrompe se o salvamento do pedido falhar
                }

                // Se chegou aqui, o SalesOrder FOI salvo com status Pendente no banco.
                Notification::make()->success()->title('Pedido Atualizado')->body("O pedido {$currentSalesOrder->order_number} foi atualizado para Pendente.")->send();

                // 5. CRUCIAL: Atualize o $this->record novamente DEPOIS de salvar o SalesOrder.
                // Isso garante que $this->record->salesOrder na página agora reflita
                // o estado persistido (com status 'Pendente').
                $this->refreshRecordData();

                // 6. Atualiza e salva a SalesVisit
                $this->record->status = SalesVisit::STATUS_COMPLETED;
                $this->record->visit_end_time = now();
                if (!$this->record->visited_at) {
                    $this->record->visited_at = $this->record->visit_end_time;
                }

                if (!$this->record->save()) {
                    Notification::make()->danger()->title('Erro ao Finalizar Visita')->body("Não foi possível salvar as alterações na visita.")->send();
                    // Neste ponto, o SalesOrder foi atualizado, mas a SalesVisit não.
                    // Você pode querer adicionar lógica para lidar com essa inconsistência,
                    // mas por enquanto, apenas notificamos e retornamos.
                    return;
                }

                // 7. Notificação final e redirecionamento
                Notification::make()->success()->title('Visita Finalizada!')->body('A visita foi concluída com sucesso.')->send();
                $this->redirect(SalesVisitResource::getUrl('index'));
            })
            ->visible(fn(): bool => $this->record &&
                $this->record->status === SalesVisit::STATUS_IN_PROGRESS &&
                $this->record->salesOrder &&
                isset($this->record->salesOrder->items_count) &&
                $this->record->salesOrder->items_count > 0);
    }


    protected function getHeaderActions(): array
    {
        if (!$this->record) {
            return [];
        }
        // Atualiza o registro e suas relações relevantes antes de renderizar as ações
        $this->refreshRecordData();
        if ($this->record->sales_order_id) { // Só tenta carregar salesOrder se o ID existir
            // Garante que salesOrder e items_count estejam carregados/atualizados
            // Usar loadMissing para evitar recarregar desnecessariamente
            $this->record->loadMissing(['salesOrder' => fn($query) => $query->withCount('items')]);
        }

        $actions = [];
        $actions[] = Action::make('startVisitButton')
            ->label('Iniciar Visita')
            ->action('startVisit')
            ->color('primary')
            ->icon('heroicon-o-play-circle')
            ->visible(fn(): bool => $this->record && $this->record->status === SalesVisit::STATUS_SCHEDULED);

        $actions[] = $this->createSalesOrderAction();
        $actions[] = $this->rescheduleVisitAction();

        // Adiciona as novas ações de finalização condicionalmente
        $actions[] = $this->finalizeVisitWithoutOrderAction();
        $actions[] = $this->finalizeVisitWithEmptyOrderAction();
        $actions[] = $this->finalizeVisitWithOrderAction();

        return $actions;
    }
}
