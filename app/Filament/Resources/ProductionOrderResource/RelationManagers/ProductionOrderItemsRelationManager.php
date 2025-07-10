<?php

namespace App\Filament\Resources\ProductionOrderResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;
// Adicione a importação do Model se for usar no ->url()
// use App\Models\ProductionOrderItem;
// use App\Filament\Resources\ProductResource;


class ProductionOrderItemsRelationManager extends RelationManager
{
    // Relação definida no Model ProductionOrder
    protected static string $relationship = 'items'; // <-- CORRIGIDO AQUI

    // Título da seção na página da Ordem de Produção
    protected static ?string $title = 'Itens da Ordem';

    // Atributo do MODELO RELACIONADO (ProductionOrderItem) usado como título (não muito útil aqui)
    // Vamos usar product.name na tabela
    // protected static ?string $recordTitleAttribute = 'product.name'; // Removido, definiremos na tabela

    /**
     * Formulário para CRIAR ou EDITAR um ProductionOrderItem.
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_uuid')
                    ->label('Produto')
                    ->relationship('product', 'name') // Busca na relação 'product' do ProductionOrderItem
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpan(2), // Ocupa 2 colunas

                TextInput::make('quantity_planned')
                    ->label('Qtd. Planejada')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(1)
                    ->columnSpan(['default' => 2, 'lg' => 1]),

                TextInput::make('quantity_produced')
                    ->label('Qtd. Produzida')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->readOnly()
                    ->columnSpan(['default' => 2, 'lg' => 1]),

                Textarea::make('notes')
                    ->label('Observações do Item')
                    ->columnSpan(['default' => 2, 'lg' => 1]),
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
                // ->url(fn (ProductionOrderItem $record): string => ProductResource::getUrl('edit', ['record' => $record->product_uuid])) // Opcional: Link para editar o produto

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
                    ->modalHeading('Adicionar Item à Ordem de Produção')
                    ->using(function (array $data, RelationManager $livewire) {
                        $parent = $livewire->getOwnerRecord();
                        $item = $parent->items()
                            ->withTrashed() // Inclui soft-deletados
                            ->where('product_uuid', $data['product_uuid'])
                            ->first();

                        if ($item) {
                            if ($item->trashed()) {
                                $item->restore();
                                $item->quantity_planned = $data['quantity_planned'];
                            } else {
                                $item->quantity_planned += $data['quantity_planned'];
                            }
                            $item->notes = $data['notes'] ?? $item->notes;
                            $item->save();
                            return $item;
                        }

                        // Cria novo se não existir
                        return $parent->items()->create($data);
                    }),
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

    // Removido pois não há sub-relações definidas aqui
    // public function getRelations(): array
    // {
    //     return [
    //         ProductionLogsRelationManager::class,
    //     ];
    // }
}
