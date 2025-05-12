<?php

namespace App\Filament\Resources\SalesOrderResource\RelationManagers;

use App\Models\Product;
use App\Models\SalesOrderItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB; // Para cálculos se necessário diretamente

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $recordTitleAttribute = 'product.name'; // Usará o nome do produto como título

    protected static ?string $modelLabel = 'Item do Pedido';
    protected static ?string $pluralModelLabel = 'Itens do Pedido';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Produto')
                    ->relationship('product', 'name', modifyQueryUsing: fn (Builder $query) => $query->orderBy('name'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        if ($state) {
                            $product = Product::find($state);
                            if ($product) {
                                $set('unit_price', $product->sale_price ?? 0);
                                // Limpar desconto ao mudar produto
                                $set('discount_amount', 0);
                            }
                        } else {
                            $set('unit_price', 0);
                            $set('discount_amount', 0);
                        }
                    })
                    ->distinct() // Garante que o mesmo produto não possa ser adicionado múltiplas vezes
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems() // Se usar dentro de um repeater, previne duplicados
                    ->columnSpan(2),

                Forms\Components\TextInput::make('quantity')
                    ->label('Quantidade')
                    ->numeric()
                    ->required()
                    ->default(1)
                    ->minValue(0.0001) // Ajuste conforme sua necessidade de precisão
                    ->step('any') // Permite decimais
                    ->reactive()
                    ->columnSpan(1),

                Forms\Components\TextInput::make('unit_price')
                    ->label('Preço Unitário (R$)')
                    ->numeric()
                    ->required()
                    ->prefix('R$')
                    ->inputMode('decimal')
                    ->reactive()
                    ->columnSpan(1),

                Forms\Components\TextInput::make('discount_amount')
                    ->label('Desconto (R$)')
                    ->numeric()
                    ->prefix('R$')
                    ->inputMode('decimal')
                    ->default(0)
                    ->minValue(0)
                    ->reactive()
                    ->helperText('Desconto aplicado ao preço unitário.')
                    ->rules([
                        fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                            $productId = $get('product_id');
                            $unitPrice = (float)($get('unit_price') ?? 0);
                            $discount = (float)($value ?? 0);

                            if ($productId) {
                                $product = Product::find($productId);
                                if ($product && $product->minimum_sale_price !== null) {
                                    $minPrice = (float)$product->minimum_sale_price;
                                    if (($unitPrice - $discount) < $minPrice) {
                                        $fail("O preço final com desconto (R$ " . number_format($unitPrice - $discount, 2, ',', '.') . ") não pode ser menor que o preço mínimo de venda do produto (R$ " . number_format($minPrice, 2, ',', '.') . ").");
                                    }
                                }
                            }
                            if (($unitPrice - $discount) < 0) {
                                $fail('O desconto não pode ser maior que o preço unitário.');
                            }
                        },
                    ])
                    ->columnSpan(1),

                Forms\Components\Placeholder::make('final_price_placeholder')
                    ->label('Preço Final Unitário (R$)')
                    ->content(function (Get $get): string {
                        $unitPrice = (float)($get('unit_price') ?? 0);
                        $discount = (float)($get('discount_amount') ?? 0);
                        $finalPrice = max(0, $unitPrice - $discount); // Garante que não seja negativo
                        return 'R$ ' . number_format($finalPrice, 2, ',', '.');
                    })
                    ->columnSpan(1),

                Forms\Components\Placeholder::make('total_price_placeholder')
                    ->label('Preço Total do Item (R$)')
                    ->content(function (Get $get): string {
                        $quantity = (float)($get('quantity') ?? 0);
                        $unitPrice = (float)($get('unit_price') ?? 0);
                        $discount = (float)($get('discount_amount') ?? 0);
                        $finalPrice = max(0, $unitPrice - $discount);
                        $totalPrice = $quantity * $finalPrice;
                        return 'R$ ' . number_format($totalPrice, 2, ',', '.');
                    })
                    ->columnSpan(2), // Ocupa mais espaço

                Forms\Components\Textarea::make('notes')
                    ->label('Observações do Item')
                    ->rows(2)
                    ->columnSpanFull(),
            ])->columns(3); // Ajuste o número de colunas conforme preferir
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.name')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produto')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qtd.')
                    ->numeric(decimalPlaces: 4) // Ajuste a precisão conforme a migration
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->label('Preço Unit.')
                    ->money('BRL')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->label('Desconto')
                    ->money('BRL')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('final_price')
                    ->label('Preço Final Unit.')
                    ->money('BRL')
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Subtotal Item')
                    ->money('BRL')
                    ->alignEnd()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('BRL')->label('Total Geral')), // Soma o total dos itens
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // O modelo SalesOrderItem já calcula final_price e total_price no evento 'creating'
                        // Não precisamos fazer aqui, mas poderíamos se quiséssemos.
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // O modelo SalesOrderItem já recalcula no evento 'updating'
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'asc'); // Ou product.name
    }

    /**
     * Sobrescreve o método para garantir que o company_id seja definido
     * com base no company_id do pedido pai (SalesOrder).
     */
    protected function handleRecordCreation(array $data): Model
    {
        $ownerRecord = $this->getOwnerRecord(); // Pega o SalesOrder
        $data['company_id'] = $ownerRecord->company_id;

        // O modelo SalesOrderItem já tem a lógica para calcular os preços
        // e para definir o company_id se não vier da ordem.
        // Apenas garantimos que o company_id da ordem seja usado aqui.
        return static::getModel()::create($data);
    }

    /**
     * Sobrescreve o método para garantir que o company_id não seja alterado
     * e permaneça o do pedido pai (SalesOrder).
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $ownerRecord = $this->getOwnerRecord();
        $data['company_id'] = $ownerRecord->company_id; // Garante que o company_id não mude

        // O modelo SalesOrderItem já tem a lógica para recalcular os preços
        $record->update($data);
        return $record;
    }
}
