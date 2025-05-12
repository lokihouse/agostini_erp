<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs; // Adicionado para usar abas
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Validation\Rule;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $modelLabel = 'Produto';
    protected static ?string $pluralModelLabel = 'Produtos';
    protected static ?string $navigationGroup = 'Cadastros';
    protected static ?int $navigationSort = 22;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('ProductTabs')
                    ->tabs([
                        Tabs\Tab::make('Informações Principais')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nome')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('sku')
                                    ->label('SKU')
                                    ->maxLength(255)
                                    ->columnSpan(1),
                                Forms\Components\Select::make('unit_of_measure')
                                    ->label('Unidade de Medida')
                                    ->options([
                                        'unidade' => 'Unidade (un)',
                                        'peça' => 'Peça (pç)',
                                        'metro' => 'Metro (m)',
                                        'kg' => 'Quilograma (kg)',
                                        'litro' => 'Litro (l)',
                                    ])
                                    ->required()
                                    ->default('unidade')
                                    ->searchable()
                                    ->columnSpan(1),
                                Forms\Components\Textarea::make('description')
                                    ->label('Descrição')
                                    ->columnSpanFull(),
                            ])->columns(2), // Mantém 2 colunas para esta aba

                        Tabs\Tab::make('Custos e Preços')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Forms\Components\TextInput::make('standard_cost')
                                    ->label('Custo Padrão')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->maxValue(42949672.95)
                                    ->default(null)
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('sale_price')
                                    ->label('Preço de Venda')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->maxValue(42949672.95)
                                    ->default(null)
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('minimum_sale_price')
                                    ->label('Preço Mínimo de Venda')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->maxValue(42949672.95)
                                    ->default(null)
                                    ->helperText('Usado para validar descontos em pedidos de venda.')
                                    ->columnSpan(2), // Pode ocupar 2 colunas ou 1, conforme preferir
                            ])->columns(2), // Define 2 colunas para esta aba
                    ])->columnSpanFull(), // Faz as abas ocuparem a largura total
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_of_measure')
                    ->label('Un. Medida')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('standard_cost')
                    ->label('Custo Padrão')
                    ->money('BRL')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Ocultar por padrão para não poluir
                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Preço Venda')
                    ->money('BRL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('minimum_sale_price')
                    ->label('Preço Mín. Venda')
                    ->money('BRL')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Ocultar por padrão
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
            ->defaultSort('name', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductionStepsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
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
        return ['name', 'sku'];
    }
}
