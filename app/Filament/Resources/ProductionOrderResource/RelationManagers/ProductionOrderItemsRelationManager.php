<?php

namespace App\Filament\Resources\ProductionOrderResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;


class ProductionOrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Itens da Ordem';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_uuid')
                    ->label('Produto')
                    ->relationship('product', 'name', fn (Builder $query) => $query->orderBy('name'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function (callable $set, $state) {
                        // Se nenhum produto for selecionado, limpa as etapas
                        if (blank($state)) {
                            $set('production_steps_info', []);
                            return;
                        }

                        // Busca o produto selecionado
                        $product = Product::find($state);

                        // Obtém os nomes das etapas para exibição
                        $stepNames = $product?->productionSteps()->pluck('name')->toArray() ?? [];

                        // Define o estado para exibição informativa
                        $set('production_steps_info', $stepNames);
                    })
                    ->columnSpanFull(),

                // CAMPO INFORMATIVO: Mostra as etapas que serão criadas automaticamente
                Forms\Components\Placeholder::make('production_steps_info')
                    ->label('Etapas de Produção (Geradas Automaticamente)')
                    ->content(function (callable $get) {
                        $stepNames = $get('production_steps_info') ?? [];
                        
                        if (empty($stepNames)) {
                            return 'Selecione um produto para ver as etapas.';
                        }
                        
                        return implode(', ', $stepNames);
                    })
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('quantity_planned')
                    ->label('Qtd. Planejada')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(1)
                    ->columnSpan(1),

                Forms\Components\Textarea::make('notes')
                    ->label('Observações do Item')
                    ->columnSpan(1),
            ])->columns(2);
    }

    /**
     * Tabela para LISTAR os ProductionOrderItems desta ProductionOrder.
     */
    public function table(Table $table): Table
    {
        return $table
            // ->recordTitleAttribute('product.name') // Define o atributo usado para identificar o registro (opcional)
            ->columns([
                TextColumn::make('product.name') // Acessa o nome através da relação 'product' no ProductionOrderItem
                ->label('Produto')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('productionSteps.name') // Mostra as etapas na tabela
                ->label('Etapas')
                    ->badge()
                    ->limitList(2)
                    ->searchable(),

                TextColumn::make('product.sku') // Mostrar SKU do produto
                ->label('SKU')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true), // Esconder por padrão

                TextColumn::make('product.unit_of_measure') // Mostrar Unidade de Medida
                ->label('Un.')
                    ->toggleable(isToggledHiddenByDefault: true), // Esconder por padrão

                TextColumn::make('quantity_planned')
                    ->label('Qtd. Planejada')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('quantity_produced')
                    ->label('Qtd. Produzida')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Observações')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->notes)
                    ->toggleable(isToggledHiddenByDefault: true), // Esconder por padrão
            ])
            ->filters([
                // Filtros se necessário
            ])
            ->headerActions([
                // Ação para CRIAR um novo ProductionOrderItem associado a esta ProductionOrder
                Tables\Actions\CreateAction::make()
                    ->label('Adicionar Item')
                    ->modalHeading('Adicionar Item à Ordem de Produção'),
            ])
            ->actions([
                // Ação para EDITAR um ProductionOrderItem existente
                Tables\Actions\EditAction::make()
                    ->modalHeading('Editar Item da Ordem'),
                // Ação para DELETAR um ProductionOrderItem
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('product.name', 'asc'); // Ordenar por nome do produto
    }
}
