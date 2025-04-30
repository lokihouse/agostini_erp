<?php

namespace App\Filament\Pages;

use App\Models\PedidoDeVenda;
use App\Models\Produto;
use App\Models\ProdutosPorPedidoDeVenda;
use App\Models\Visita;
use App\Utils\Cnpj;
use Barryvdh\Debugbar\Facades\Debugbar;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;

class RegistroDeVisita extends Page implements HasInfolists
{
    use InteractsWithInfolists;
    protected static bool $shouldRegisterNavigation = false;
    protected ?string $heading = '';
    protected static ?string $slug = 'registro-de-visita/{id}';
    protected static string $view = 'filament.pages.registro-de-visita';

    public static function getRelativeRouteName(): string
    {
        return "registro-de-visita";
    }

    public Visita $record;

    public $produtos = [];
    public function mount($id): void
    {
        $this->record = (new Visita())->FindOrFail($id);
    }

    public function clienteInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record->cliente)
            ->schema([
                TextEntry::make('cnpj')->formatStateUsing(fn($state) => Cnpj::format($state)),
                TextEntry::make('razao_social'),
                TextEntry::make('nome_fantasia'),
                TextEntry::make('telefone')->default('-'),
                TextEntry::make('email')->default('-'),
            ]);
    }

    public function checkInVisitaAction(): Action
    {
        return Action::make('checkInVisita')
            ->extraAttributes(['class' => 'w-full'])
            ->requiresConfirmation()
            ->label('Informar Visita')
            ->color('success')
            ->action(function () {
                $this->record->status = "em andamento";
                $this->record->save();
            });
    }

    public function encerrarVisitaSemPedidoAction(): Action
    {
        return Action::make('encerrarVisitaSemPedido')
            ->color('danger')
            ->extraAttributes(['class' => 'w-full'])
            ->requiresConfirmation()
            ->icon('heroicon-o-no-symbol')
            ->modalIcon('heroicon-o-no-symbol')
            ->label('Encerrar visita sem pedido')
            ->form([
                RichEditor::make('descricao')
                    ->required()
                    ->label('Faça uma escrição detalhada da visita.'),
                Repeater::make('plano_de_acoes')
                    ->label('Plano de ações')
                    ->defaultItems(0)
                    ->addActionLabel('Adicionar ação')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('o_que_fazer')->required(),
                        TextInput::make('quem')->required(),
                        DatePicker::make('prazo')->required()
                    ])
            ])
            ->action(function ($data) {
                $this->record->relatorio_de_visita = json_encode($data);
                $this->record->status = 'finalizada';
                $this->record->save();
            });
    }

    public function finalizarPedidoAction(): Action
    {
        return Action::make('finalizarPedido')
            ->color('success')
            ->icon('heroicon-o-check-badge')
            ->extraAttributes(['class' => 'w-full rounded-t-none'])
            ->requiresConfirmation()
            ->hidden(empty($this->produtos))
            ->label('Finalizar pedido e visita')
            ->action(function () {


                $pedido_de_venda = new PedidoDeVenda();
                $pedido_de_venda->user_id = auth()->user()->id;
                $pedido_de_venda->cliente_id = $this->record->cliente_id;
                $pedido_de_venda->visita_id = $this->record->id;
                $pedido_de_venda->save();

                foreach ($this->produtos as $produto) {
                    $produtoPorPedidoDeVenda = new ProdutosPorPedidoDeVenda();
                    $produtoPorPedidoDeVenda->pedido_de_venda_id = $pedido_de_venda->id;
                    unset($produto['produto_nome']);
                    $produtoPorPedidoDeVenda->fill($produto);
                    $produtoPorPedidoDeVenda->save();
                }

                $this->record->pedido_de_venda_id = $pedido_de_venda->id;
                $this->record->status = 'finalizada';
                $this->record->save();
            });
    }

    public function classeTituloPorStatus()
    {
        switch ($this->record->status) {
            case 'agendada':
                return 'p-2 border border-gray-600 bg-gray-200 text-gray-800';
            case 'em andamento':
                return 'p-2 border border-amber-600 bg-amber-200 text-amber-800';
            case 'finalizada':
                return 'p-2 border border-blue-600 bg-blue-200 text-blue-800';
        }
    }

    public function recalcularValores(Set $set, Get $get)
    {
        $quantidade = intval($get('quantidade')) ?? 0;
        $produto =  Produto::query()->find($get('produto'));

        if($produto) $produto = $produto->toArray();
        else return;

        $valor_minimo = $produto['valor_minimo_venda'] ?? 0;
        $valor_nominal = $produto['valor_nominal_venda'] ?? 0;
        $desconto = (100 - floatval($get('desconto') ?? 0)) / 100;

        $set('valor_original', $valor_nominal);

        $valor_final = $valor_nominal * $desconto;

        $subtotal = $valor_final * $quantidade;

        if($valor_minimo > $valor_final){
            $set('error', 'Valor menor que mínimo autorizado!<br/><small>' . Number::currency($valor_minimo, 'BRL') . "</small>");
            $set('subtotal', null);
            $set('total', null);
        }else{
            $set('error', null);
            $set('subtotal', $valor_final);
            $set('total', $subtotal);
        }
    }
    public function removeById($id)
    {
        unset($this->produtos[$id]);
    }
    public function adicionarProdutoAction(): Action
    {
        return Action::make('adicionarProduto')
            ->label('Adicionar produto ao pedido')
            ->extraAttributes(['class' => 'w-full'])
            ->icon('heroicon-o-plus')
            ->requiresConfirmation()
            ->modalHeading('')
            ->modalDescription(null)
            ->modalIcon(null)
            ->steps([
                Step::make('Adicionar Produto')
                    ->icon('heroicon-o-gift')
                    ->columns(['default' => 4])
                    ->schema([
                        TextInput::make('quantidade')
                            ->label('Qnt.')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $this->recalcularValores($set, $get);
                            })
                            ->numeric(),
                        Select::make('produto')
                            ->columnSpan(3)
                            ->required()
                            ->searchable()
                            ->options(Produto::query()->pluck('nome', 'id'))
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $this->recalcularValores($set, $get);
                            }),
                        Placeholder::make('valor_original')
                            ->columnSpan(2)
                            ->live()
                            ->label('R$ Original')
                            ->extraAttributes(['class' => 'w-full border bg-gray-200 rounded-xl p-1.5 text-center'])
                            ->content(fn ($state): string => Number::currency($state ?? 0, "BRL")),
                        TextInput::make('desconto')
                            ->default(0)
                            ->columnSpan(2)
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $this->recalcularValores($set, $get);
                            })
                            ->suffix("%"),
                        Placeholder::make('subtotal')
                            ->columnSpan(2)
                            ->live()
                            ->label('R$ Subtotal')
                            ->extraAttributes(['class' => 'w-full border bg-gray-200 rounded-xl p-1.5 text-center'])
                            ->content(fn ($state): string => Number::currency($state ?? 0, "BRL")),
                        Placeholder::make('total')
                            ->columnSpan(2)
                            ->live()
                            ->label('R$ Total')
                            ->extraAttributes(['class' => 'w-full border bg-gray-200 rounded-xl p-1.5 text-center'])
                            ->content(fn ($state): string => Number::currency($state ?? 0, "BRL")),
                        Placeholder::make('error')
                            ->label('')
                            ->extraAttributes(['class' => 'w-full bg-red-200 border-2 rounded p-2 text-center'])
                            ->columnSpan(4)
                            ->visible(fn($state) => !!$state)
                            ->content(fn($state) => new HtmlString($state))
                    ])
            ])
            ->action(function(Get $get, $data) {
                $produto = Produto::query()->find($data["produto"]);
                $obj = [
                    "produto_id" => $data["produto"],
                    "produto_nome" => $produto->nome,
                    "quantidade" => intval($data["quantidade"]),
                    "valor_original" => ($produto->valor_nominal_venda ?? 0 ),
                    "desconto" => Number::format(floatval($data["desconto"]), 2),
                    "valor_final" => ($produto->valor_nominal_venda ?? 0 ) * ((100 - floatval($data["desconto"])) / 100),
                    "subtotal" => intval($data["quantidade"]) * ($produto->valor_nominal_venda ?? 0 ) * ((100 - floatval($data["desconto"])) / 100),
                ];

                $this->produtos[(string)Str::uuid()] = $obj;
            });
    }
}
