<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;

// Importar o Relation Manager que vamos criar
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Filament\Resources\ProductResource\RelationManagers\ProductionStepsRelationManager;

// Adicionar esta linha
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

// Necessário para a regra unique
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;

// Para agrupar campos
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Validation\Rule;

// Para filtro de excluídos

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube'; // Ícone mais relacionado a produto
    protected static ?string $modelLabel = 'Produto'; // Nome singular
    protected static ?string $pluralModelLabel = 'Produtos'; // Nome plural
    protected static ?string $navigationGroup = 'Produção';
    protected static ?int $navigationSort = 24; // Ordem na navegação

    // Configuração para busca global (opcional)
    protected static ?string $recordTitleAttribute = 'name'; // Campo usado na busca global

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informações Principais') // Agrupa campos visualmente
                ->columns(2) // Divide a seção em 2 colunas
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome') // Traduzir label
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1), // Ocupa 1 coluna
                    Forms\Components\TextInput::make('sku')
                        ->label('SKU')
                        ->maxLength(255)
                        ->columnSpan(1), // Ocupa 1 coluna
                    Forms\Components\Select::make('unit_of_measure') // Mudar para Select
                    ->label('Unidade de Medida')
                        ->options([ // Definir opções
                            'unidade' => 'Unidade (un)',
                            'peça' => 'Peça (pç)',
                            'metro' => 'Metro (m)',
                            'kg' => 'Quilograma (kg)',
                            'litro' => 'Litro (l)',
                            // Adicione outras conforme necessário
                        ])
                        ->required()
                        ->default('unidade')
                        ->searchable() // Permite buscar nas opções
                        ->columnSpan(1),
                    Forms\Components\Textarea::make('description')
                        ->label('Descrição')
                        ->columnSpanFull(), // Ocupa a largura total dentro da seção
                ]),

                // Seção para custos/preços (opcional)
                /* Descomente se tiver os campos standard_cost e sale_price no model/migration
                Section::make('Custos e Preços')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('standard_cost')
                            ->label('Custo Padrão')
                            ->numeric()
                            ->prefix('R$') // Adiciona prefixo de moeda
                            ->maxValue(42949672.95) // Exemplo de limite
                            ->default(null),
                        Forms\Components\TextInput::make('sale_price')
                            ->label('Preço de Venda')
                            ->numeric()
                            ->prefix('R$')
                            ->maxValue(42949672.95)
                            ->default(null),
                    ])
                */
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Tables\Columns\TextColumn::make('uuid') // Geralmente não mostramos UUID na tabela principal
                //     ->label('UUID')
                //     ->searchable()
                //     ->toggleable(isToggledHiddenByDefault: true), // Esconder por padrão
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome') // Traduzir label
                    ->searchable()
                    ->sortable(), // Permitir ordenação
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_of_measure')
                    ->label('Un. Medida') // Label mais curto
                    ->searchable()
                    ->sortable(),
                /* Descomente se tiver os campos standard_cost e sale_price
                Tables\Columns\TextColumn::make('standard_cost')
                    ->label('Custo Padrão')
                    ->money('BRL') // Formata como moeda brasileira
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Preço Venda')
                    ->money('BRL')
                    ->sortable(),
                */
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em') // Traduzir
                    ->dateTime('d/m/Y H:i') // Formato brasileiro
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Manter escondido por padrão
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em') // Traduzir
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Excluído em') // Traduzir
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(), // Adiciona filtro para ver excluídos (SoftDeletes)
            ])
            ->actions([
                Tables\Actions\ViewAction::make(), // Adicionar ação de visualizar
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), // Adicionar ação de deletar individual
                Tables\Actions\RestoreAction::make(), // Adicionar ação de restaurar (SoftDeletes)
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(), // Excluir permanentemente (SoftDeletes)
                    Tables\Actions\RestoreBulkAction::make(), // Restaurar em massa (SoftDeletes)
                ]),
            ])
            // Ordenação padrão
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

    // Necessário para o filtro TrashedFilter funcionar corretamente
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    // Opcional: Configuração para busca global mais específica
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'sku']; // Campos usados na busca global
    }
}
