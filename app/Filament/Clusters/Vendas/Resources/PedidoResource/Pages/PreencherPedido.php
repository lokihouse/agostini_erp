<?php

namespace App\Filament\Clusters\Vendas\Resources\PedidoResource\Pages;

use App\Filament\Clusters\Vendas\Resources\PedidoResource;
use App\Models\Produto;
use App\Models\Visita;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class PreencherPedido extends EditRecord
{
    protected static string $resource = PedidoResource::class;
    protected static ?string $breadcrumb = 'Preencher';
    public function getHeading(): string|Htmlable
    {
        $record = $this->getRecord();
        return "Pedido #" . $record->id . " - " . $record->visita->cliente->nome_fantasia;
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns(12)
            ->schema([
                Group::make([
                    TextInput::make('Vendedor')
                        ->formatStateUsing(function() {
                            return Visita::query()->where('id', $this->getRecord()->visita_id)->first()->responsavel->name;
                        })
                        ->disabled(),
                    TextInput::make('status')->disabled(),
                    TextInput::make('valor_total_pedido')
                        ->label('Valor Total')
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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);

        $valorTotal = null;

        $data['itens_de_pedido'] = (array)json_decode($data['itens_de_pedido']);
        foreach ($data['itens_de_pedido'] as $key => $item) {
            $data['itens_de_pedido'][$key] = (array)$item;
            $valorTotal += $data['itens_de_pedido'][$key]['valor_total'];
        }

        $data['valor_total_pedido'] = $valorTotal;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);

        $record = $this->getRecord();
        $record->itens_de_pedido = $data['itens_de_pedido'];
        $record->save();

        return $data;
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
