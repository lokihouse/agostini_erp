<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransportOrderResource\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\TransportOrderResource\Pages;
use App\Models\TransportOrder;
// Imports não utilizados foram comentados para clareza, mas podem ser necessários para outras partes do seu código.
// use App\Models\User;
// use App\Models\Vehicle;
// use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;  // Adicionado para type hint
use Illuminate\Database\Eloquent\SoftDeletingScope;
// use Illuminate\Support\Carbon;

class TransportOrderResource extends Resource
{
    protected static ?string $model = TransportOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $modelLabel = 'Ordem de Transporte';

    protected static ?string $pluralModelLabel = 'Ordens de Transporte';

    protected static ?string $navigationGroup = 'Cargas';

    protected static ?int $navigationSort = 71;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->disabled(fn(?Model $record): bool => $record instanceof TransportOrder && $record->status === TransportOrder::STATUS_COMPLETED)
                    ->schema([
                        Forms\Components\TextInput::make('transport_order_number')
                            ->label('Número da OT')
                            ->disabled()
                            ->placeholder('Gerado automaticamente')
                            ->dehydrated(false)
                            ->visibleOn('edit'),
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->disabled()  // Já estava, crucial
                                ->options([
                                    TransportOrder::STATUS_PENDING => 'Pendente',
                                    TransportOrder::STATUS_APPROVED => 'Aprovada',
                                    TransportOrder::STATUS_IN_PROGRESS => 'Em Andamento',
                                    TransportOrder::STATUS_COMPLETED => 'Concluída',
                                    TransportOrder::STATUS_CANCELLED => 'Cancelada',
                                ])
                                ->required()
                                ->default(TransportOrder::STATUS_PENDING)
                                ->live(),
                            Forms\Components\Select::make('vehicle_id')
                                ->label('Veículo')
                                ->relationship('vehicle', 'license_plate', fn(Builder $query) => $query->where('is_active', true))
                                ->searchable(['license_plate', 'description'])
                                ->preload(),
                            Forms\Components\Select::make('driver_id')
                                ->label('Motorista')
                                ->relationship('driver', 'name', fn(Builder $query) => $query->whereHas('roles', fn($q) => $q->whereIn('name', ['Motorista', config('filament-shield.super_admin.name')])))
                                ->searchable()
                                ->preload(),
                        ]),
                    ]),
                Forms\Components\Group::make()
                    ->disabled(fn(?Model $record): bool => $record instanceof TransportOrder && $record->status === TransportOrder::STATUS_COMPLETED)
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\DateTimePicker::make('planned_departure_datetime')
                                ->label('Saída Prevista')
                                ->native(false),
                            Forms\Components\DateTimePicker::make('actual_departure_datetime')
                                ->label('Saída Efetiva')
                                ->readOnly()
                                ->disabled()
                                ->native(false),
                            Forms\Components\DateTimePicker::make('planned_arrival_datetime')
                                ->label('Chegada Prevista (Última Entrega)')
                                ->native(false),
                            Forms\Components\DateTimePicker::make('actual_arrival_datetime')
                                ->label('Chegada Efetiva (Última Entrega)')
                                ->readOnly()
                                ->disabled()
                                ->native(false),
                        ]),
                    ]),
                Forms\Components\Group::make()
                    ->disabled(fn(?Model $record): bool => $record instanceof TransportOrder && $record->status === TransportOrder::STATUS_COMPLETED)
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Observações Gerais da Ordem')
                            ->columnSpanFull()
                            ->rows(5),
                    ]),
                Forms\Components\Group::make()
                    ->columnSpanFull()
                    ->visible(fn(callable $get) => $get('status') === TransportOrder::STATUS_CANCELLED)
                    ->schema([
                        Forms\Components\Textarea::make('cancellation_reason')
                            ->label('Motivo do Cancelamento')
                            ->requiredIf('status', TransportOrder::STATUS_CANCELLED)
                            ->rows(3),
                        Forms\Components\DateTimePicker::make('cancelled_at')
                            ->label('Data do Cancelamento')
                            ->default(now())
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('cancelled_by_user_id')
                            ->label('Cancelado Por')
                            ->relationship('cancelledBy', 'name')
                            ->default(fn() => auth()->id())
                            ->disabled()
                            ->dehydrated(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transport_order_number')
                    ->label('Número OT')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        TransportOrder::STATUS_PENDING => 'warning',
                        TransportOrder::STATUS_APPROVED => 'info',
                        TransportOrder::STATUS_IN_PROGRESS => 'primary',
                        TransportOrder::STATUS_COMPLETED => 'success',
                        TransportOrder::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        TransportOrder::STATUS_PENDING => 'Pendente',
                        TransportOrder::STATUS_APPROVED => 'Aprovada',
                        TransportOrder::STATUS_IN_PROGRESS => 'Em Andamento',
                        TransportOrder::STATUS_COMPLETED => 'Concluída',
                        TransportOrder::STATUS_CANCELLED => 'Cancelada',
                        default => ucfirst($state),
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle.license_plate')
                    ->label('Veículo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('driver.name')
                    ->label('Motorista')
                    ->searchable(),
                Tables\Columns\TextColumn::make('planned_departure_datetime')
                    ->label('Saída Prevista')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        TransportOrder::STATUS_PENDING => 'Pendente',
                        TransportOrder::STATUS_APPROVED => 'Aprovada',
                        TransportOrder::STATUS_IN_PROGRESS => 'Em Andamento',
                        TransportOrder::STATUS_COMPLETED => 'Concluída',
                        TransportOrder::STATUS_CANCELLED => 'Cancelada',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Action::make('approveShipment')
                    ->label('Aprovar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (TransportOrder $record) {
                        if ($record->status === TransportOrder::STATUS_PENDING) {
                            $record->update(['status' => TransportOrder::STATUS_APPROVED]);
                            Notification::make()
                                ->title('Ordem Aprovada')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Ação não permitida')
                                ->body('Esta ordem não pode ser aprovada no status atual.')
                                ->warning()
                                ->send();
                        }
                    })
                    ->visible(function (TransportOrder $record): bool {  // MODIFICADO AQUI
                        $record->loadMissing('items');
                        return $record->status === TransportOrder::STATUS_PENDING && $record->items->isNotEmpty();
                    }),
                Action::make('downloadShipmentPdf')
                    ->label('Imprimir Documento')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn(TransportOrder $record): string => route('transport-orders.pdf', ['uuid' => $record->uuid]))
                    ->openUrlInNewTab()
                    ->visible(function (TransportOrder $record): bool {
                        $record->loadMissing('items');
                        if ($record->items->isEmpty()) {
                            return false;
                        }
                        foreach ($record->items as $item) {
                            if (is_null($item->delivery_sequence)) {
                                return false;
                            }
                        }
                        return true;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListTransportOrders::route('/'),
            'create' => Pages\CreateTransportOrder::route('/create'),
            'edit' => Pages\EditTransportOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

