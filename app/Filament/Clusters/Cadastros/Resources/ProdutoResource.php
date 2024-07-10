<?php

namespace App\Filament\Clusters\Cadastros\Resources;

use App\Filament\Clusters\Cadastros;
use App\Filament\Clusters\Cadastros\Resources\ProdutoResource\Pages;
use App\Filament\Clusters\Cadastros\Resources\ProdutoResource\RelationManagers;
use App\Filament\ResourceBase;
use App\Forms\Components\ProdutoEtapaField;
use App\Forms\Components\ProdutoMapaField;
use App\Models\Departamento;
use App\Models\Produto;
use App\Models\ProdutoEtapa;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ProdutoResource extends ResourceBase
{
    protected static ?string $model = Produto::class;
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Cadastros::class;

    public static function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Cadastro')
                            ->schema([
                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('nome')
                                        ->label('Nome')
                                        ->columnSpan(3)
                                        ->required(),
                                    Forms\Components\MarkdownEditor::make('descricao')
                                        ->columnSpan(7)
                                        ->label('Descrição'),
                                ])->columns(10)->columnSpanFull()
                            ]),
                        Tabs\Tab::make('Produção')
                            ->hidden($form->getOperation() === 'create')
                            ->columns(10)
                            ->schema([
                                Forms\Components\Group::make([
                                    Actions::make([
                                        Actions\Action::make('add_etapa')
                                            ->label('Adicionar Etapa de Produção')
                                            ->action(function (Get $get, Set $set, $state, $data, $record) use ($form) {
                                                $etapa = new ProdutoEtapa();
                                                $etapa->produto_id = $state['id'];
                                                $etapa->departamento_id_origem = $data['departamento_de_origem'];
                                                $etapa->insumos = json_encode($data['insumos']);
                                                $etapa->departamento_id_destino = $data['departamento_de_trabalho'];
                                                $etapa->producao = json_encode($data['producao']);
                                                $etapa->save();
                                                $record->refresh();
                                            })
                                            ->steps([
                                                Step::make('Origem')
                                                    ->schema([
                                                        Forms\Components\Group::make([
                                                            Select::make('departamento_de_origem')
                                                                ->required()
                                                                ->searchable()
                                                                ->columnSpan(4)
                                                                ->options(function () {
                                                                    return Departamento::query()->pluck('nome', 'id')->toArray();
                                                                }),
                                                            Repeater::make('insumos')
                                                                ->required()
                                                                ->columnSpan(6)
                                                                ->addActionLabel("Adicionar")
                                                                ->simple(
                                                                    TextInput::make('material')
                                                                )
                                                        ])->columns(10),
                                                    ]),
                                                Step::make('Trabalho')
                                                    ->columns(10)
                                                    ->schema([
                                                        Select::make('departamento_de_trabalho')
                                                            ->required()
                                                            ->searchable()
                                                            ->columnSpan(4)
                                                            ->options(function () {
                                                                return Departamento::query()->pluck('nome', 'id')->toArray();
                                                            }),
                                                        Repeater::make('producao')
                                                            ->addActionLabel("Adicionar")
                                                            ->label('Produção')
                                                            ->required()
                                                            ->columnSpan(6)
                                                            ->simple(
                                                                TextInput::make('material')
                                                            )
                                                    ]),
                                            ]),
                                        Actions\Action::make('view_map')
                                            ->label('Mapa de Produção')
                                            ->color('gray')
                                            ->modalSubmitAction(false)
                                            ->modalCancelActionLabel('Fechar')
                                            ->modalContent(function (Get $get, $record) {
                                                return view(
                                                    'forms.components.produto-mapa-field',
                                                    [
                                                        'etapas' => $record->etapas->toArray(),
                                                    ]
                                                );
                                            })
                                    ]),
                                ])
                                    ->columnSpanFull(),
                                Forms\Components\Group::make([
                                    ProdutoEtapaField::make('etapas_tabela'),
                                    Placeholder::make('mapa_producao')
                                        ->content(fn ($record) => new HtmlString("<img src='" . $record->mapa_de_producao . "'/>"))
                                ])->columnSpan(8),
                                Forms\Components\Group::make([
                                    TextInput::make('tempo_de_producao')
                                        ->label('Tempo de Produção'),
                                ])->columnSpan(2),
                            ]),
                        Tabs\Tab::make('Financeiro'),
                        Tabs\Tab::make('Vendas')
                            ->schema([
                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('valor_unitario')
                                        ->label('Valor Unitário')
                                        ->prefix('R$')
                                        ->mask(RawJs::make('$money($input, \',\', \'.\')'))
                                        ->stripCharacters('.')
                                        ->required()
                                        ->columnSpan(2),
                                ])->columns(10)->columnSpanFull()
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->defaultSort('nome','asc')
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable()
                    ->extraHeaderAttributes(['style' => 'width: 200px']),
                Tables\Columns\TextColumn::make('descricao')
                    ->limit(90),
                Tables\Columns\TextColumn::make('valor_unitario')
                    ->formatStateUsing(fn ($state) => 'R$ ' . number_format($state, 2, ',', '.'))
                    ->extraHeaderAttributes(['style' => 'width: 100px']),
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
