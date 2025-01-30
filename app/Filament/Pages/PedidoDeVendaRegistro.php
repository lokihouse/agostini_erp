<?php

namespace App\Filament\Pages;

use App\Models\PedidoDeVenda;
use App\Models\Produto;
use App\Models\Visita;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use function Laravel\Prompts\alert;

class PedidoDeVendaRegistro extends Page implements HasForms, HasActions, HasInfolists
{
    use InteractsWithInfolists;
    use InteractsWithForms;
    use InteractsWithActions;

    protected static ?string $title = 'Pedido de Venda :: Registro';
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static string $view = 'filament.pages.pedido-de-venda-registro';
    protected static bool $shouldRegisterNavigation = false;
    protected ?string $heading = '';
    protected static ?string $slug = 'pedido-de-venda/{id}/registro/';
    public static function getRelativeRouteName(): string
    {
        return "pedido-de-venda.registro";
    }

    public PedidoDeVenda $record;

    public function mount($id): void
    {
        $this->record = (new PedidoDeVenda())->FindOrFail($id);
    }

    public function recalcularValores(Set $set, Get $get)
    {
        $quantidade = intval($get('quantidade')) ?? 0;
        $produto =  Produto::query()->find($get('produto'));

        if($produto) $produto = $produto->toArray();
        else return;


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

    public function finalizarPedidoAction(): Action
    {
        return Action::make('finalizarPedido')
            ->extraAttributes(['class' => 'w-full'])
            ->label('Finalizar Pedido')
            ->color('success')
            ->requiresConfirmation()
            ->action(function () {
                $this->record->status = 'fechado';
                $this->record->save();

                $visita = Visita::query()->where('id', $this->record->visita_id)->first();
                $visita->status = 'realizada';
                $visita->save();
            });
    }

    public function removeById($id)
    {
        $arr = json_decode($this->record->produtos, true);
        unset($arr[$id]);
        $this->record->produtos = json_encode($arr);
        $this->record->save();
    }
    public function adicionarProdutoAction(): Action
    {
        return Action::make('adicionarProduto')
            ->extraAttributes(['class' => 'w-full'])
            ->label('Adicionar produto')
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
                        MoneyInput::make('valor_original')
                            ->disabled()
                            ->columnSpan(2),
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
                        MoneyInput::make('subtotal')
                            ->disabled()
                            ->columnSpan(2),
                        MoneyInput::make('total')
                            ->disabled()
                            ->columnSpan(2),
                        Placeholder::make('error')
                            ->label('')
                            ->extraAttributes(['class' => 'w-full bg-red-200 border-2 rounded p-2 text-center'])
                            ->columnSpan(4)
                            ->visible(fn($state) => !!$state)
                            ->content(fn($state) => new HtmlString($state))
                    ])
            ])
            ->action(function(Get $get, $data) {
                $obj = [
                    "quantidade" => $data["quantidade"],
                    "desconto" => Number::format(floatval($data["desconto"]), 2),
                    "produto_id" => $data["produto"],
                    "valor_original" => (Produto::query()->find($data["produto"])->first()->valor_nominal_venda ?? 0 ) / 100
                ];

                $arr = json_decode($this->record->produtos, true);
                $arr[(string)Str::uuid()] = $obj;
                $this->record->produtos = json_encode($arr);
                $this->record->save();
            });
    }
}
