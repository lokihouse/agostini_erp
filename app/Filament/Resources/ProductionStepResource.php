<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionStepResource\Pages;
use App\Filament\Resources\ProductionStepResource\RelationManagers\WorkSlotsRelationManager;
use App\Models\ProductionStep;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Validation\Rule; // <-- Import Rule

class ProductionStepResource extends Resource
{
    protected static ?string $model = ProductionStep::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver'; // Ícone mais relacionado a processo/etapa
    protected static ?string $modelLabel = 'Etapa de Produção'; // Nome singular
    protected static ?string $pluralModelLabel = 'Etapas de Produção'; // Nome plural
    protected static ?string $navigationGroup = 'Produção';
    protected static ?int $navigationSort = 31; // Ordem na navegação (depois de Produtos)

    // Configuração para busca global
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detalhes da Etapa')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome da Etapa')
                            ->required()
                            ->maxLength(255)
                            ->rule(function ($record) {
                                $companyId = auth()->user()?->company_id;
                                if (!$companyId) {
                                    return 'Falha ao obter ID da empresa para validação.';
                                }

                                return Rule::unique('production_steps', 'name')
                                ->where('company_id', $companyId)
                                ->ignore($record?->uuid, 'uuid');
                            })
                            // --- FIM DA VALIDAÇÃO ---
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('default_order')
                            ->label('Ordem Padrão')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Ordem sugerida ao adicionar a produtos (0 ou vazio para nenhuma).')
                            ->default(null)
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->columnSpanFull(),
                    ])
            ]);
    }

    // ... (método table, getRelations, getPages, getEloquentQuery) ...
    // Mantenha o restante do código como estava
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome da Etapa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->description)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('default_order')
                    ->label('Ordem Padrão')
                    ->numeric()
                    ->sortable()
                    ->placeholder('-'),
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
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Excluído em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('default_order', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            WorkSlotsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductionSteps::route('/'),
            'create' => Pages\CreateProductionStep::route('/create'),
            'edit' => Pages\EditProductionStep::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description'];
    }
}
