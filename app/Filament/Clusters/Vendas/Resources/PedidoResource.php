<?php

namespace App\Filament\Clusters\Vendas\Resources;

use App\Filament\Actions\PedidoCancelar;
use App\Filament\Actions\PedidoGerarOrdemDeProducao;
use App\Filament\Clusters\Vendas;
use App\Filament\Clusters\Vendas\Resources\PedidoResource\Pages;
use App\Filament\Clusters\Vendas\Resources\PedidoResource\RelationManagers;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Visita;
use Faker\Provider\Text;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PedidoResource extends Resource
{
    protected static ?string $model = Pedido::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $cluster = Vendas::class;
    protected static ?string $navigationGroup = 'Cadastros';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(12)
            ->schema([
                Group::make([
                    TextInput::make('visita_id')
                        ->hidden(),
                    TextInput::make('Vendedor')
                        ->formatStateUsing(function(Get $get, $state) {
                            // dd('as', $state, $get('visita_id'));
                            return Visita::query()->where('id', $get('visita_id'))->first()->responsavel->name;
                        })
                        ->disabled(),
                    TextInput::make('status')->disabled(),
                    TextInput::make('valor_total_pedido')
                        ->label('Valor Total')
                        ->formatStateUsing(function ($state){
                            return number_format($state, 2, '.', '');
                        })
                        ->prefix('R$')
                        ->disabled(),
                ])
                    ->columnSpan(2),
                Group::make([
                    Repeater::make('itens_de_pedido')
                        ->columnSpanFull()
                        ->defaultItems(0)
                        ->collapsible()
                        ->collapsed()
                        ->columns(14)
                        ->itemLabel(function (array $state): ?string {
                            if(empty($state['quantidade'])) return null;
                            return ($state['quantidade'] . 'x ' . $state['produto_nome']);
                        })
                        ->afterStateUpdated(function(Get $get, Set $set) use ($form) {
                            $itens = $get('itens_de_pedido');
                            if(empty($itens)) {
                                $form->getLivewire()->data['valor_total_pedido'] = number_format(0, 2, '.', '');
                            }else{
                                foreach ($itens as $item) {
                                    if(!empty($item['quantidade']) && !empty($item['produto'])) {
                                        self::calculateItemTotal($item['quantidade'], $item['produto'], $set, $form);
                                    }
                                }
                            }
                        })
                        ->schema([
                            TextInput::make('quantidade')
                                ->numeric()
                                ->minValue(1)
                                ->columnSpan(2)
                                ->live()
                                ->required()
                                ->afterStateUpdated(function(Get $get, Set $set) use ($form) {
                                    $quantidade = $get('quantidade');
                                    $produto_id = $get('produto');
                                    self::calculateItemTotal($quantidade, $produto_id, $set, $form);
                                }),
                            TextInput::make('produto_nome')
                                ->hidden(),
                            Select::make('produto')
                                ->live()
                                ->columnSpan(8)
                                ->required()
                                ->options(Produto::query()->pluck('nome', 'id')->toArray())
                                ->afterStateUpdated(function(Get $get, Set $set) use ($form) {
                                    $quantidade = $get('quantidade');
                                    $produto_id = $get('produto');
                                    self::calculateItemTotal($quantidade, $produto_id, $set, $form);
                                }),
                            TextInput::make('valor_unitario')
                                ->label('R$ Un.')
                                ->prefix('R$')
                                ->columnSpan(2)
                                ->readOnly(),
                            TextInput::make('valor_total')
                                ->label('R$ Total')
                                ->prefix('R$')
                                ->columnSpan(2)
                                ->readOnly()
                            ,
                        ])
                ])
                    ->columnSpan(10),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->badge()
                    ->extraHeaderAttributes(['style' => 'width: 125px;'])
                    ->color(fn (Pedido $record) => match ($record->status) {
                        'pendente' => 'gray',
                        'confirmado' => 'info',
                        'em producao' => 'warning',
                        'finalizado' => 'success',
                        'cancelado' => 'danger',
                    }),
                TextColumn::make('visita.cliente.nome_fantasia')
                    ->label('Cliente'),
                TextColumn::make('confirmacao')
                    ->date('d/m/Y')
                    ->extraHeaderAttributes(['style' => 'width: 125px', 'class' => 'justify-center'])
                    ->label('Confirmação'),
                TextColumn::make('producao')
                    ->date('d/m/Y')
                    ->extraHeaderAttributes(['style' => 'width: 125px', 'class' => 'justify-center'])
                    ->label('Produção'),
                TextColumn::make('entrega')
                    ->date('d/m/Y')
                    ->extraHeaderAttributes(['style' => 'width: 125px', 'class' => 'justify-center'])
                    ->label('Entrega'),
                TextColumn::make('itens_de_pedido')
                    ->label('Total Pedido')
                    ->extraHeaderAttributes(['style' => 'width: 125px', 'class' => 'justify-center'])
                    ->formatStateUsing(function($state){
                        $state = json_decode($state);
                        $total = 0;
                        foreach ($state as $item) {
                            $total += $item->valor_total;
                        }
                        return $total;
                    })
            ])
            ->actionsPosition(Tables\Enums\ActionsPosition::BeforeCells)
            ->actions([
                PedidoGerarOrdemDeProducao::make('gerar_ordem_de_producao'),
                PedidoCancelar::make('cancelar_pedido')
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
            'index' => Pages\ListPedidos::route('/'),
            //'create' => Pages\CreatePedido::route('/create'),
            //'edit' => Pages\EditPedido::route('/{record}/edit'),
            'preencher' => Pages\PreencherPedido::route('/{record}/preencher'),
        ];
    }

    private static function calculateItemTotal($quantidade, $produto_id, Set $set, Form $form): void
    {
        // $quantidade = $get('quantidade');
        // $produto_id = $get('produto');
        if(is_numeric($quantidade) && is_numeric($produto_id)) {
            $produto = Produto::query()->where('id', $produto_id)->first();
            $set('produto_nome', $produto->nome);
            $set('valor_unitario', $produto->valor_unitario);
            $set('valor_total',  number_format($produto->valor_unitario * $quantidade, 2, '.', ''));
        }

        $itens = $form->getLivewire()->data['itens_de_pedido'];
        $valor_total_itens = null;
        foreach ($itens as $item) {
            if(!empty($item['valor_total'])) $valor_total_itens += $item['valor_total'];
        }

        if(!empty($valor_total_itens)) {
            $form->getLivewire()->data['valor_total_pedido'] = number_format($valor_total_itens, 2, '.', '');
        }
    }
}
