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
                    // Validação: Garantir que o mesmo produto não seja adicionado duas vezes na MESMA ordem
                    // A regra 'unique' básica do Filament já lida com ignoreRecord=true
                    ->unique(
                        table: 'production_order_items', // Tabela a verificar
                        column: 'product_uuid', // Coluna a verificar
                        ignoreRecord: true // Ignora o registro atual ao editar
                    )
                    // Condição extra: A unicidade só se aplica DENTRO da ordem atual
                    // Usando closure para acessar o registro pai (OwnerRecord)
                    ->rule(function (RelationManager $livewire) {
                        return Rule::unique('production_order_items', 'product_uuid')
                            ->where('production_order_uuid', $livewire->getOwnerRecord()->uuid)
                            // O ignoreRecord: true acima já deve lidar com a edição.
                            // Se precisar ignorar manualmente em cenários específicos:
                            // ->ignore($livewire->getRecord()?->uuid);
                            ;
                    })
                    ->columnSpan(2), // Ocupa 2 colunas

                TextInput::make('quantity_planned')
                    ->label('Qtd. Planejada')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(1)
                    ->columnSpan(1),

                TextInput::make('quantity_produced')
                    ->label('Qtd. Produzida')
                    ->numeric()
                    ->minValue(0)
                    ->default(0) // Produzido começa em 0
                    ->readOnly() // Geralmente a quantidade produzida é atualizada por logs, não editada aqui
                    ->columnSpan(1),

                Textarea::make('notes')
                    ->label('Observações do Item')
                    ->columnSpanFull(), // Ocupa largura total
            ])->columns(2); // Define 2 colunas para o layout do formulário
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

    // Removido pois não há sub-relações definidas aqui
    // public function getRelations(): array
    // {
    //     return [
    //         ProductionLogsRelationManager::class,
    //     ];
    // }
}
