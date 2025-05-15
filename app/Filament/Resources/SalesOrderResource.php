<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesOrderResource\Pages;
use App\Filament\Resources\SalesOrderResource\RelationManagers\ItemsRelationManager; // Corrigido
use App\Models\Client;
use App\Models\SalesOrder;
use App\Models\SalesVisit;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form; // Mantido para o type hint do método principal
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException; // Para capturar erros de transição

class SalesOrderResource extends Resource
{
    protected static ?string $model = SalesOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $modelLabel = 'Pedido de Venda';
    protected static ?string $pluralModelLabel = 'Pedidos de Venda';
    protected static ?string $navigationGroup = 'Vendas';
    protected static ?int $navigationSort = 41;

    protected static ?string $recordTitleAttribute = 'order_number';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('SalesOrderTabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Informações do Pedido')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\Placeholder::make('order_number_placeholder')
                                    ->label('Número do Pedido')
                                    ->content(fn (?SalesOrder $record): string => $record?->order_number ?? 'Será gerado automaticamente')
                                    ->columnSpan(1)
                                    ->hiddenOn('create'),

                                Forms\Components\Select::make('client_id')
                                    ->label('Cliente')
                                    ->relationship('client', 'name', modifyQueryUsing: fn (Builder $query) => $query->orderBy('name'))
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->required()
                                    ->columnSpan(fn (string $operation) => $operation === 'create' ? 2 : 1),

                                Forms\Components\Select::make('sales_visit_id')
                                    ->label('Visita de Venda Associada')
                                    ->options(function (Get $get, ?SalesOrder $record): array {
                                        $clientId = $get('client_id');
                                        if (!$clientId) {
                                            return [];
                                        }
                                        $query = SalesVisit::query()
                                            ->where('client_id', $clientId)
                                            ->where('status', '!=', SalesVisit::STATUS_CANCELLED)
                                            ->where('status', '!=', SalesVisit::STATUS_RESCHEDULED)
                                            ->where(function (Builder $q) {
                                                $q->whereNotNull('visited_at')
                                                    ->orWhere('scheduled_at', '<', now());
                                            });
                                        if ($record && $record->sales_visit_id) {
                                            $query->where(function(Builder $q) use ($record) {
                                                $q->whereNull('sales_order_id')
                                                    ->orWhere('uuid', $record->sales_visit_id);
                                            });
                                        } else {
                                            $query->whereNull('sales_order_id');
                                        }
                                        return $query->orderBy('scheduled_at', 'desc')
                                            ->limit(50)
                                            ->get()
                                            ->mapWithKeys(function (SalesVisit $visit) use ($record) { // Passar $record
                                                $label = "Visita em " . Carbon::parse($visit->scheduled_at)->format('d/m/Y H:i');
                                                if ($visit->notes) {
                                                    $label .= " (" . Str::limit($visit->notes, 30) . ")";
                                                }
                                                if ($visit->sales_order_id && $visit->sales_order_id !== $record?->uuid) {
                                                    $label .= " [Vinculada a outro pedido]";
                                                }
                                                return [$visit->uuid => $label];
                                            })->all();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->disabled(fn (Get $get): bool => !$get('client_id'))
                                    ->nullable()
                                    ->columnSpan(1),
                                Forms\Components\DatePicker::make('delivery_deadline')
                                    ->label('Prazo de Entrega')
                                    ->native(false)
                                    ->nullable()
                                    ->columnSpan(1),

                                Forms\Components\Placeholder::make('status_placeholder')
                                    ->label('Status Atual do Pedido')
                                    ->content(fn (?SalesOrder $record): string => $record ? (SalesOrder::getStatusOptions()[$record->status] ?? ucfirst($record->status)) : SalesOrder::getStatusOptions()[SalesOrder::STATUS_PENDING])
                                    ->columnSpan(1)
                                    ->hiddenOn('create'),

                                Forms\Components\Placeholder::make('user_id_placeholder')
                                    ->label('Criado Por')
                                    ->content(fn (?SalesOrder $record): string => $record?->user?->name ?? auth()->user()->name)
                                    ->columnSpan(1)
                                    ->hiddenOn('create'),

                                Forms\Components\Textarea::make('notes')
                                    ->label('Observações do Pedido')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Forms\Components\Placeholder::make('total_amount_placeholder')
                                    ->label('Valor Total do Pedido')
                                    ->content(fn (?SalesOrder $record): string => $record ? 'R$ ' . number_format($record->total_amount, 2, ',', '.') : 'R$ 0,00')
                                    ->columnSpanFull()
                                    ->hiddenOn('create'),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Cancelamento do Pedido')
                            ->icon('heroicon-o-x-circle')
                            ->visible(fn (?SalesOrder $record): bool => $record && $record->status === SalesOrder::STATUS_CANCELLED)
                            ->schema([
                                Forms\Components\TextInput::make('cancellation_reason')
                                    ->label('Motivo do Cancelamento')
                                    ->disabled()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('cancellation_details')
                                    ->label('Detalhes do Cancelamento')
                                    ->disabled()
                                    ->rows(4)
                                    ->columnSpanFull(),
                                Forms\Components\DateTimePicker::make('cancelled_at')
                                    ->label('Data do Cancelamento')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visible(fn (?SalesOrder $record) => $record && $record->cancelled_at)
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Nº Pedido')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_date')
                    ->label('Data Pedido')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivery_deadline')
                    ->label('Prazo Entrega')
                    ->date('d/m/Y')
                    ->placeholder('N/A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Valor Total')
                    ->money('BRL')
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => SalesOrder::getStatusOptions()[$state] ?? ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        SalesOrder::STATUS_PENDING => 'warning',
                        SalesOrder::STATUS_APPROVED => 'success',
                        SalesOrder::STATUS_PROCESSING => 'info',
                        SalesOrder::STATUS_SHIPPED => 'primary',
                        SalesOrder::STATUS_DELIVERED => 'success',
                        SalesOrder::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Criado Por')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('client_id')
                    ->label('Cliente')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(SalesOrder::getStatusOptions()),
                Tables\Filters\Filter::make('order_date')
                    ->form([
                        Forms\Components\DatePicker::make('order_date_from')->label('Pedido de'),
                        Forms\Components\DatePicker::make('order_date_until')->label('Pedido até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['order_date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('order_date', '>=', $date),
                            )
                            ->when(
                                $data['order_date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('order_date', '<=', $date),
                            );
                    }),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // REMOVIDAS DAQUI:
                // self::getApproveOrderAction(),
                // self::getCancelOrderAction(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('order_date', 'desc');
    }

    public static function getApproveOrderAction(): Tables\Actions\Action // Mantido como static para ser chamado pela Page
    {
        return Tables\Actions\Action::make('approveOrder')
            ->label('Aprovar Pedido')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Aprovar Pedido de Venda')
            ->modalDescription('Tem certeza que deseja aprovar este pedido? Uma Ordem de Produção será gerada.')
            // A visibilidade será controlada na Page agora, usando o registro da página
            // ->visible(fn (SalesOrder $record): bool => $record->status === SalesOrder::STATUS_PENDING)
            ->action(function (SalesOrder $record): void { // $record será passado pela Page
                try {
                    $record->status = SalesOrder::STATUS_APPROVED;
                    $record->save();

                    Notification::make()
                        ->title('Pedido Aprovado')
                        ->body("O pedido {$record->order_number} foi aprovado e uma Ordem de Produção foi gerada.")
                        ->success()
                        ->send();
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
                    \Illuminate\Support\Facades\Log::error('Erro ao aprovar pedido: ' . $e->getMessage(), ['exception' => $e, 'sales_order_uuid' => $record->uuid]);
                }
            });
    }

    public static function getCancelOrderAction(): Tables\Actions\Action // Mantido como static
    {
        return Tables\Actions\Action::make('cancelOrder')
            ->label('Cancelar Pedido')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Cancelar Pedido de Venda')
            ->modalDescription('Informe o motivo e detalhes do cancelamento. Esta ação não pode ser desfeita facilmente.')
            // A visibilidade será controlada na Page agora
            // ->visible(fn (SalesOrder $record): bool => $record->status !== SalesOrder::STATUS_CANCELLED)
            ->form([
                Forms\Components\TextInput::make('cancellation_reason')
                    ->label('Motivo do Cancelamento')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('cancellation_details')
                    ->label('Detalhes Adicionais (Opcional)')
                    ->rows(3),
            ])
            ->action(function (SalesOrder $record, array $data): void { // $record será passado pela Page
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
                    \Illuminate\Support\Facades\Log::error('Erro ao cancelar pedido: ' . $e->getMessage(), ['exception' => $e, 'data' => $data, 'sql' => method_exists($e, 'getSql') ? $e->getSql() : 'N/A']);
                    Notification::make()
                        ->title('Erro ao Cancelar Pedido')
                        ->danger()
                        ->body($errorMessage)
                        ->send();
                }
            });
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesOrders::route('/'),
            'create' => Pages\CreateSalesOrder::route('/create'),
            'edit' => Pages\EditSalesOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where('status', '!=', SalesOrder::STATUS_DRAFT);
    }
}
