<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionOrderResource\Pages;
use App\Filament\Resources\ProductionOrderResource\RelationManagers;
use App\Models\ProductionOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;

// --- IMPORT NECESSÁRIO ---
use Filament\Tables\Columns\ProgressBarColumn;
// --- FIM IMPORT ---

class ProductionOrderResource extends Resource
{
    protected static ?string $model = ProductionOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $modelLabel = 'Ordem de Produção';
    protected static ?string $pluralModelLabel = 'Ordens de Produção';
    protected static ?string $navigationGroup = 'Produção';
    protected static ?int $navigationSort = 34;

    protected static ?string $recordTitleAttribute = 'order_number';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detalhes da Ordem')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->label('Número da Ordem')
                            ->maxLength(255)
                            ->readOnly()
                            ->hiddenOn('create')
                            ->columnSpan(1),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->hiddenOn('create')
                            ->options([
                                'Pendente' => 'Pendente',
                                'Planejada' => 'Planejada',
                                'Liberada' => 'Liberada',
                                'Em Andamento' => 'Em Andamento',
                                'Pausada' => 'Pausada',
                                'Concluída' => 'Concluída',
                                'Cancelada' => 'Cancelada',
                            ])
                            ->required()
                            ->default('Pendente')
                            ->searchable()
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('due_date')
                            ->label('Data Limite')
                            ->native(false)
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('start_date')
                            ->label('Data Início Real')
                            ->readOnly()
                            ->native(false)
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('completion_date')
                            ->label('Data Conclusão Real')
                            ->readOnly()
                            ->native(false)
                            ->columnSpan(1),

                        Forms\Components\Select::make('user_uuid')
                            ->label('Responsável')
                            ->relationship('user', 'name', function (Builder $query) {
                                $query
                                    ->where('company_id', auth()->user()->company_id)
                                    ->where('is_active', true);
                            })
                            ->default(auth()->user()->uuid)
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('notes')
                            ->label('Observações Gerais')
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Nº Ordem')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pendente' => 'gray',
                        'Planejada' => 'info',
                        'Liberada' => 'primary',
                        'Em Andamento' => 'warning',
                        'Pausada' => 'danger',
                        'Concluída' => 'success',
                        'Cancelada' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                ViewColumn::make('progress_view')
                ->label('Progresso')
                    ->view('filament.tables.columns.progress-bar')
                    ->visible(function (?ProductionOrder $record): bool {
                        if (!$record) {
                            return true; // Mostra o cabeçalho da coluna
                        }
                        $hasPlanned = ($record->items_sum_quantity_planned ?? 0) > 0;
                        $hasStarted = in_array($record->status, ['Em Andamento', 'Pausada', 'Concluída']);
                        return $hasPlanned && $hasStarted;
                    }),
                // --- FIM COLUNA DE PROGRESSO ---

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Data Limite')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Responsável')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Pendente' => 'Pendente',
                        'Planejada' => 'Planejada',
                        'Liberada' => 'Liberada',
                        'Em Andamento' => 'Em Andamento',
                        'Pausada' => 'Pausada',
                        'Concluída' => 'Concluída',
                        'Cancelada' => 'Cancelada',
                    ])
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->url(fn (ProductionOrder $record): string => route('production-orders.pdf', $record->uuid))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\RestoreAction::make(),
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
            RelationManagers\ProductionOrderItemsRelationManager::class,
            RelationManagers\ProductionOrderLogsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductionOrders::route('/'),
            'create' => Pages\CreateProductionOrder::route('/create'),
            'edit' => Pages\EditProductionOrder::route('/{record}/edit'),
        ];
    }

    /**
     * Modifica a query Eloquent base para a tabela.
     * Adiciona withSum para calcular totais dos itens.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            // Adiciona as somas das colunas da relação 'items'
            ->withSum('items', 'quantity_planned') // Gera 'items_sum_quantity_planned'
            ->withSum('items', 'quantity_produced'); // Gera 'items_sum_quantity_produced'
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['order_number', 'status', 'user.name'];
    }
}
