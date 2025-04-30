<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdutoResource\Pages;
use App\Filament\Resources\ProdutoResource\RelationManagers;
use App\Forms\Components\Produto_EtapasDeProducao_Form;
use App\Models\Produto;
use App\Models\ProdutoEtapa;
use Barryvdh\Debugbar\Facades\Debugbar;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Support\RawJs;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Money\Number;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use Ramsey\Uuid\Guid\Guid;

class ProdutoResource extends ResourceBase
{
    protected static ?string $model = Produto::class;
    protected static ?string $navigationGroup = 'Cadastro';
    protected static ?int $navigationSort = 12;
    protected static ?string $label = 'Produto';
    protected static ?string $pluralLabel = 'Produtos';
    protected static ?string $navigationIcon = 'heroicon-o-gift';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(4)
            ->schema([
                Group::make([
                    TextInput::make('nome')
                        ->label('Nome')
                        ->required()
                ]),
                Tabs::make('')
                    ->columnSpan(3)
                    ->schema([
                        Tabs\Tab::make('Produção')
                            ->hidden($form->getOperation() === 'create')
                            ->columns(4)
                            ->schema([
                                Produto_EtapasDeProducao_Form::make('produto_etapas')
                                    ->live()
                                    ->label('Etapas de Produção')
                                    ->columnSpanFull()
                            ]),
                        Tabs\Tab::make('Vendas')
                            ->columns(4)
                            ->schema([
                                TextInput::make('valor_minimo_venda')
                                    ->columnSpan(1)
                                    ->label('Valor Mínimo de Venda')
                                    ->prefix('R$')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->extraAlpineAttributes([ 'x-mask:dynamic' => '$money($input, \',\')'])
                                    ->stripCharacters(',')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('valor_nominal_venda')
                                    ->columnSpan(1)
                                    ->label('Valor Nominal de Venda')
                                    ->prefix('R$')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->extraAlpineAttributes([ 'x-mask:dynamic' => '$money($input, \',\')'])
                                    ->stripCharacters(',')
                                    ->numeric()
                                    ->required(),
                            ]),
                        Tabs\Tab::make('Cargas')
                            ->columns(4)
                            ->schema([
                                Repeater::make('volumes')
                                    ->columnSpanFull()
                                    ->reorderable(false)
                                    ->grid(3)
                                    ->columns(2)
                                    ->live()
                                    ->schema([
                                        TextInput::make('largura')->required()->suffix('cm')->numeric(),
                                        TextInput::make('altura')->required()->suffix('cm')->numeric(),
                                        TextInput::make('comprimento')->required()->suffix('cm')->numeric(),
                                        TextInput::make('peso')->required()->suffix('Kg')->numeric(),
                                    ])
                                    ->itemLabel(function (array $state): HtmlString {
                                        return new HtmlString('Volume<br/><span class="text-xs font-extralight" >' . ($state["largura"] ?? '__') . "." . ($state["altura"] ?? '__' ) . "." . ($state["comprimento"] ?? '__' ) . " cm &#x2022; " . ($state["peso"] ?? '__') . "Kg" . '</span>');
                                    })
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome'),
                TextColumn::make('valor_nominal_venda')
                    ->label('R$ Venda')
                    ->money('BRL')
                    ->width(1)
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
            'index' => Pages\ListProdutos::route('/'),
            'create' => Pages\CreateProduto::route('/create'),
            'edit' => Pages\EditProduto::route('/{record}/edit'),
        ];
    }
}
