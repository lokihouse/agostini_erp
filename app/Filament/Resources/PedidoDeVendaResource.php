<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PedidoResource\Pages;
use App\Filament\Resources\PedidoResource\RelationManagers;
use App\Models\Pedido;
use App\Models\PedidoDeVenda;
use App\Models\Produto;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Illuminate\Support\Number;

class PedidoDeVendaResource extends ResourceBase
{
    protected static ?string $model = PedidoDeVenda::class;
    protected static ?string $navigationGroup = 'Vendas';
    protected static ?int $navigationSort = 31;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(4)
            ->disabled(fn($record) => $record && $record->status !== 'novo')
            ->schema([
                Group::make([
                    Select::make('cliente_id')
                        ->relationship('cliente', 'nome_fantasia'),
                    Select::make('user_id')
                        ->relationship('vendedor', 'nome')
                ]),
                Repeater::make('produtos')
                    ->grid(3)
                    ->addable(false)
                    ->deletable(false)
                    ->disabled(fn($record) => $record && $record->status !== 'novo')
                    ->columns(3)
                    ->schema([
                        TextInput::make('quantidade')
                            ->label('Qnt.')
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn($set, $get) => PedidoDeVendaResource::recalcularValores($get, $set))
                            ->required()
                            ->numeric(),
                        Select::make('produto_id')
                            ->columnSpan(2)
                            ->searchable()
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn($set, $get) => PedidoDeVendaResource::recalcularValores($get, $set))
                            ->preload()
                            ->required()
                            ->relationship('produto', 'nome'),
                        TextInput::make('valor_original')
                            ->label('Un')
                            ->disabled()
                            ->numeric(),
                        TextInput::make('desconto')
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn($set, $get) => PedidoDeVendaResource::recalcularValores($get, $set))
                            ->numeric()
                            ->default(Number::format(0,2)),
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->disabled()
                            ->numeric(),
                    ])
                    ->reorderable(false)
                    ->cloneable(false)
                    ->collapsible(false)
                    ->columnSpan(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->width(50),
                TextColumn::make('status')
                    ->badge()
                    ->width(100),
                TextColumn::make('cliente.nome_fantasia')
                    ->width(300),
                TextColumn::make('vendedor.nome'),
                TextColumn::make('vendedor.meta_mensal_de_venda')
                    ->label('Meta de Vendas')
                    ->formatStateUsing(fn($state) => Number::format(floatval($state) / 100, 2))
                    ->hidden(),
                TextColumn::make('produtos')
                    ->label('Num de Produtos')
                    ->formatStateUsing(fn ($state) => is_array($decoded = json_decode($state, true)) ? count($decoded) : 0)
                    ->width(1),
                TextColumn::make('valor_de_produtos')
                    ->label('Total')
                    ->alignCenter()
                    ->width(1),
            ])
            ->filters([
                SelectFilter::make('cliente_id')
                    ->relationship('cliente', 'nome_fantasia'),
                SelectFilter::make('user_id')
                    ->relationship('vendedor', 'nome'),
            ])
            ->bulkActions([
                ExportBulkAction::make()
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPedidosDeVenda::route('/'),
            'create' => Pages\CreatePedidoDeVenda::route('/create'),
            'edit' => Pages\EditPedidoDeVenda::route('/{record}/edit'),
        ];
    }

    public static function recalcularValores(Get $get, Set $set)
    {
        $quantidade = intval($get('quantidade')) ?? 0;
        $produto =  Produto::query()->find($get('produto'));

        if($produto) $produto = $produto->toArray();
        else return;

        dd($quantidade, $produto);


        $valor_nominal = (($produto['valor_nominal_venda'] ?? 0) / 100);
        $valor_minimo = (($produto['valor_minimo_venda'] ?? 0) / 100);
        $valor_final = $valor_nominal * ((100 - floatval($get('desconto') ?? 0)) / 100);
        $subtotal = $valor_final * $quantidade;

        $set('valor_original', $valor_nominal);

        if($valor_minimo > $valor_final){
            $set('error', 'Valor menor que mínimo autorizado!<br/><small>' . Number::currency($valor_minimo, 'BRL') . "</small>");
            $set('subtotal', null);
            $set('total', null);
        }else{
            $set('error', null);
            $set('subtotal', Number::format($valor_final, 2));
            $set('total', Number::format($subtotal,2));
        }
    }
}
