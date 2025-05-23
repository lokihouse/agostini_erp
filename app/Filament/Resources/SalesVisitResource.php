<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesVisitResource\Pages;
use App\Models\SalesOrder; // Usado para o link do pedido
use App\Models\SalesVisit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Carbon; // Para formatação de data, se necessário

class SalesVisitResource extends Resource
{
    protected static ?string $model = SalesVisit::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $modelLabel = 'Visita de Venda';
    protected static ?string $pluralModelLabel = 'Visitas de Venda';
    protected static ?string $navigationGroup = 'Vendas';
    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('SalesVisitTabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Detalhes da Visita')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Select::make('client_id')
                                    ->label('Cliente')
                                    ->relationship('client', 'name', modifyQueryUsing: fn (Builder $query) => $query->orderBy('name'))
                                    ->searchable()
                                    ->disabled($form->getOperation() === 'edit')
                                    ->preload()
                                    ->required(),
                                // Removido o disabled, pois o formulário padrão de edição deve permitir alterações se necessário
                                // ->disabled(fn (string $operation): bool => $operation === 'edit'),

                                Forms\Components\Select::make('assigned_to_user_id')
                                    ->label('Vendedor Responsável')
                                    ->relationship('assignedTo', 'name', modifyQueryUsing: fn (Builder $query) => $query->orderBy('name'))
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                // ->disabled(fn (string $operation): bool => $operation === 'edit'),

                                Forms\Components\DateTimePicker::make('scheduled_at')
                                    ->label('Data/Hora Agendada')
                                    ->native(false)
                                    ->seconds(false)
                                    ->required()
                                    ->default(now()->addDay()->setHour(9)->setMinute(0)),
                                // ->disabled(fn (string $operation): bool => $operation === 'edit'),

                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options(SalesVisit::getStatusOptions())
                                    ->required()
                                    ->disabled($form->getOperation() === 'edit')
                                    ->default(SalesVisit::STATUS_SCHEDULED)
                                    ->live(),
                                // ->disabled(fn (string $operation): bool => $operation === 'edit'),

                                Forms\Components\DateTimePicker::make('visited_at')
                                    ->label('Data/Hora da Realização')
                                    ->native(false)
                                    ->seconds(false)
                                    ->visible(fn (Get $get, string $operation): bool => $operation === 'edit' && in_array($get('status'), [SalesVisit::STATUS_COMPLETED])),
                                // ->disabled(fn (string $operation): bool => $operation === 'edit'),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Observações e Cancelamento')
                            ->icon('heroicon-o-pencil-square')
                            ->visible(fn (Get $get, string $operation): bool => $operation === 'edit' && in_array($get('status'), [SalesVisit::STATUS_COMPLETED, SalesVisit::STATUS_CANCELLED]))
                            ->schema([
                                Forms\Components\Textarea::make('notes')
                                    ->label('Observações da Visita')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->visible(fn (Get $get, string $operation): bool => $operation === 'edit' && $get('status') === SalesVisit::STATUS_COMPLETED),
                                // ->disabled(fn (string $operation): bool => $operation === 'edit'),

                                Forms\Components\TextInput::make('cancellation_reason')
                                    ->label('Motivo do Cancelamento')
                                    ->maxLength(255)
                                    ->visible(fn (Get $get, string $operation): bool => $operation === 'edit' && $get('status') === SalesVisit::STATUS_CANCELLED),
                                // ->disabled(fn (string $operation): bool => $operation === 'edit'),

                                Forms\Components\Textarea::make('cancellation_details')
                                    ->label('Detalhes do Cancelamento')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->visible(fn (Get $get, string $operation): bool => $operation === 'edit' && $get('status') === SalesVisit::STATUS_CANCELLED),
                                // ->disabled(fn (string $operation): bool => $operation === 'edit'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Relatório (Sem Pedido)')
                            ->icon('heroicon-o-document-chart-bar')
                            ->visible(fn (?SalesVisit $record, string $operation): bool =>
                                $operation === 'edit' &&
                                $record &&
                                $record->status === SalesVisit::STATUS_COMPLETED &&
                                is_null($record->sales_order_id) &&
                                (!empty($record->report_reason_no_order) || !empty($record->report_corrective_actions))
                            )
                            ->schema([
                                Forms\Components\TextInput::make('report_reason_no_order')
                                    ->label('Motivo da Não Geração de Pedido')
                                    ->disabled()
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('report_corrective_actions')
                                    ->label('Ações Corretivas Sugeridas')
                                    ->disabled()
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Tabs\Tab::make('Pedido Associado')
                            ->icon('heroicon-o-shopping-cart')
                            ->visible(fn (?SalesVisit $record, string $operation): bool => $operation === 'edit' && $record && $record->sales_order_id !== null)
                            ->schema([
                                Forms\Components\Placeholder::make('sales_order_info')
                                    ->label('Pedido de Venda')
                                    ->content(function (?SalesVisit $record): HtmlString|string {
                                        if ($record && $record->salesOrder) {
                                            $url = SalesOrderResource::getUrl('edit', ['record' => $record->sales_order_id]);
                                            $statusLabel = SalesOrder::getStatusOptions()[$record->salesOrder->status] ?? $record->salesOrder->status;
                                            $linkHtml = "<a href='{$url}' target='_blank' class='text-primary-600 hover:underline'>" .
                                                htmlspecialchars($record->salesOrder->order_number) .
                                                " (Status: " . htmlspecialchars($statusLabel) . ")" .
                                                "</a>";
                                            return new HtmlString($linkHtml);
                                        }
                                        return 'Nenhum pedido associado.';
                                    }),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Vendedor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Agendada Para')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => SalesVisit::getStatusOptions()[$state] ?? ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        SalesVisit::STATUS_SCHEDULED => 'warning',
                        SalesVisit::STATUS_IN_PROGRESS => 'primary', // Cor para Em Andamento
                        SalesVisit::STATUS_COMPLETED => 'success',
                        SalesVisit::STATUS_CANCELLED => 'danger',
                        SalesVisit::STATUS_RESCHEDULED => 'info',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('salesOrder.order_number')
                    ->label('Pedido Gerado')
                    ->placeholder('Nenhum')
                    ->searchable()
                    ->sortable()
                    ->url(function (SalesVisit $record): ?string {
                        if ($record->sales_order_id) {
                            return route('sales-orders.pdf', ['uuid' => $record->sales_order_id]);
                        }
                        return null;
                    }, shouldOpenInNewTab: true),
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('scheduledBy.name')
                    ->label('Agendado Por')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('visited_at')
                    ->label('Realizada Em')
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
                Tables\Filters\SelectFilter::make('assigned_to_user_id')
                    ->label('Vendedor')
                    ->relationship('assignedTo', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(SalesVisit::getStatusOptions()),
                Tables\Filters\Filter::make('scheduled_at')
                    ->form([
                        Forms\Components\DatePicker::make('scheduled_from')->label('Agendada de'),
                        Forms\Components\DatePicker::make('scheduled_until')->label('Agendada até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['scheduled_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('scheduled_at', '>=', $date),
                            )
                            ->when(
                                $data['scheduled_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('scheduled_at', '<=', $date),
                            );
                    }),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Detalhes')
                    ->hidden(fn (SalesVisit $record): bool => $record->status === SalesVisit::STATUS_COMPLETED),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->recordUrl(null
                // fn (SalesVisit $record): ?string => $record->status === SalesVisit::STATUS_COMPLETED ? null : static::getUrl('edit', ['record' => $record]),
            )
            ->defaultSort('scheduled_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // Se você tiver RelationManagers, eles entram aqui.
        ];
    }

    public static function getPages(): array
    {
        // Apenas as páginas padrão do resource
        return [
            'index' => Pages\ListSalesVisits::route('/'),
            'create' => Pages\CreateSalesVisit::route('/create'),
            'edit' => Pages\EditSalesVisit::route('/{record}/edit'),
            // Removida a página 'conduct' que existia antes
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
