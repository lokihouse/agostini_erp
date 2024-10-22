<?php

namespace App\Filament\Clusters\Cadastros\Resources;

use App\Filament\Clusters\Cadastros;
use App\Filament\Clusters\Cadastros\Resources\ProdutoResource\Pages;
use App\Filament\Clusters\Cadastros\Resources\ProdutoResource\RelationManagers;
use App\Models\Departamento;
use App\Models\Equipamento;
use App\Models\Produto;
use App\Models\ProdutoEtapa;
use App\Models\User;
use App\Utils\DateHelper;
use App\Utils\TextHelper;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ProdutoResource extends Resource
{
    protected static ?string $model = Produto::class;
    protected static ?string $navigationIcon = 'fas-code-compare';
    protected static ?string $cluster = Cadastros::class;
    protected static ?string $label = 'Produto';
    protected static ?string $pluralLabel = 'Produtos';
    protected static ?int $navigationSort = 4;
    public static function form(Form $form): Form
    {
        return $form
            ->columns(12)
            ->schema([
                Tabs::make('tabs')
                    ->columnSpanFull()
                    ->activeTab(2)
                    ->columns(12)
                    ->tabs([
                        Tab::make('Cadastro')
                            ->schema([
                                TextInput::make('nome')
                                    ->label('Nome')
                                    ->columnSpanFull()
                                    ->required(),
                                MarkdownEditor::make('descricao')
                                    ->columnSpanFull()
                                    ->label('Descrição'),
                            ]),
                        Tab::make('Produção')
                            ->schema([
                                Group::make([
                                    TextInput::make('meta_de_producao')
                                        ->mask("99:99:99")
                                        ->placeholder("HH:MM:SS")
                                        ->extraInputAttributes(['class' => 'text-center'])
                                        ->label('Meta de Produção'),
                                    Placeholder::make('tempo_de_producao')
                                        ->label('Tempo de Produção')
                                        ->extraAttributes(['class' => 'text-center'])
                                        ->content(fn ($record) => new HtmlString(DateHelper::fromSecondsToTime($record->tempo_de_producao ?? 0))),
                                ])->columnSpan(2),
                                Fieldset::make('Etapas de Produção')->schema([
                                    \App\Forms\Components\ProdutoEtapa::make('')->columnSpanFull()
                                    /*Forms\Components\Actions::make([
                                        Action::make('generateMapa')
                                            ->color('gray')
                                            ->label('Adicionar Etapa')
                                            ->modalWidth(MaxWidth::ThreeExtraLarge)
                                            ->action(function (Get $get, Set $set, $state, $data) use ($form) {
                                                $etapa = new ProdutoEtapa();
                                                $etapa->empresa_id = $state['empresa_id'];
                                                $etapa->produto_id = $state['id'];
                                                $etapa->departamento_origem_id = $data['departamento_origem'];
                                                $etapa->equipamento_origem_id = $data['equipamento_origem'];
                                                $etapa->departamento_destino_id = $data['departamento_destino'];
                                                $etapa->equipamento_destino_id = $data['equipamento_destino'];
                                                $etapa->descricao = $data['descricao'];
                                                $etapa->producao = json_encode($data['producao']);

                                                $etapa->save();

                                                Notification::make()
                                                    ->title('Etapa adicionada com sucesso! Atualize a página para visualizar.')
                                                    ->success()
                                                    ->send();
                                            })
                                            ->steps([
                                                Step::make('Informações')
                                                    ->schema([
                                                        RichEditor::make('descricao')
                                                            ->hint('O quê precisa ser feito nesse passo?')
                                                    ]),
                                                Step::make('Origem')
                                                    ->schema([
                                                        Group::make([
                                                            Select::make('departamento_origem')
                                                                ->label('Departamento')
                                                                ->required()
                                                                ->searchable()
                                                                ->live()
                                                                ->afterStateUpdated(fn (Set $set) => $set('equipamento_origem', null))
                                                                ->options(function () {
                                                                    $departamentos = Departamento::query()->get();
                                                                    return $departamentos->pluck('nome', 'id')->toArray();
                                                                }),
                                                            Select::make('equipamento_origem')
                                                                ->label('Equipamento')
                                                                ->disabled(fn (Get $get) => $get('departamento_origem') === null)
                                                                ->searchable()
                                                                ->options(function (Get $get) {
                                                                    if($get('departamento_origem') === null) {
                                                                        return [];
                                                                    }
                                                                    $equipamentos = Equipamento::query()->where('departamento_id', $get('departamento_origem'))->get();
                                                                    return $equipamentos->pluck('nome', 'id')->toArray();
                                                                }),
                                                        ])->columns(2),
                                                    ]),
                                                Step::make('Produção')
                                                    ->columns(1)
                                                    ->schema([
                                                        Repeater::make('producao')
                                                            ->defaultItems(0)
                                                            ->addActionLabel('Adicionar')
                                                            ->columns(5)
                                                            ->schema([
                                                                TextInput::make('quantidade')
                                                                    ->label('Quant.')
                                                                    ->required()
                                                                    ->integer(),
                                                                TextInput::make('descricao')
                                                                    ->label('Descrição')
                                                                    ->columnSpan(4)
                                                                    ->required(),
                                                            ]),
                                                    ]),
                                                Step::make('Destino')
                                                    ->schema([
                                                        Group::make([
                                                            Select::make('departamento_destino')
                                                                ->label('Departamento')
                                                                ->required()
                                                                ->searchable()
                                                                ->live()
                                                                ->afterStateUpdated(fn (Set $set) => $set('equipamento_destino', null))
                                                                ->options(function () {
                                                                    $departamentos = Departamento::query()->get();
                                                                    return $departamentos->pluck('nome', 'id')->toArray();
                                                                }),
                                                            Select::make('equipamento_destino')
                                                                ->label('Equipamento')
                                                                ->disabled(fn (Get $get) => $get('departamento_destino') === null)
                                                                ->searchable()
                                                                ->options(function (Get $get) {
                                                                    if($get('departamento_destino') === null) {
                                                                        return [];
                                                                    }
                                                                    $equipamentos = Equipamento::query()->where('departamento_id', $get('departamento_destino'))->get();
                                                                    return $equipamentos->pluck('nome', 'id')->toArray();
                                                                }),
                                                        ])->columns(2),
                                                    ]),
                                            ]),
                                    ])->fullWidth()->columnSpanFull(),
                                    Repeater::make('produto_etapas')
                                        ->grid(2)
                                        ->relationship('produto_etapas')
                                        ->columns(2)
                                        ->collapsed()
                                        ->live()
                                        ->defaultItems(0)
                                        ->deleteAction(function (Action $action){
                                            $action->action(function($state, array $arguments, Pages\EditProduto $livewire){
                                                ProdutoEtapa::query()->find($state[$arguments['item']]['id'])->delete();
                                                $livewire->dispatch('refresh');
                                            });
                                        })
                                        ->itemLabel(function (array $state) {
                                            $departamento_origem_nome = Departamento::query()->find($state['departamento_origem_id'])->nome;
                                            $equipamento_origem_nome = $state['equipamento_origem_id'] ? Equipamento::query()->find($state['equipamento_origem_id'])->nome : null;
                                            $departamento_destino_nome = Departamento::query()->find($state['departamento_destino_id'])->nome;
                                            $equipamento_destino_nome = $state['equipamento_destino_id'] ? Equipamento::query()->find($state['equipamento_destino_id'])->nome : null;

                                            return new HtmlString("<span class='text-xs'>ORIGEM: " . $departamento_origem_nome . ($equipamento_origem_nome ? '.' . $equipamento_origem_nome : '') . '<br/>DESTINO: ' . $departamento_destino_nome . ($equipamento_destino_nome ? '.' . $equipamento_destino_nome : '') . "</span>");
                                        })
                                        ->schema([
                                            Placeholder::make('descricao')
                                                ->columnSpanFull()
                                                ->visible(fn ($state) => $state !== null)
                                                ->content(fn ($state) => new HtmlString($state)),
                                            Placeholder::make('producao')
                                                ->label('')
                                                ->columnSpanFull()
                                                ->visible(fn ($state) => count(json_decode($state) ?? []) > 0)
                                                ->content(fn ($state) => new HtmlString(view('filament.clusters.cadastros.produto.card-producao', ['producao' => json_decode($state)]))),
                                            Placeholder::make('tempo')
                                                ->label('Tempo médio da etapa')
                                                ->content(fn ($state) => new HtmlString(DateHelper::fromSecondsToTime($state['tempo'] ?? 0)))
                                                ->columnSpanFull()
                                        ]),*/
                                ])->columnSpan(10),
                        ]),
                        Tab::make('Vendas')
                            ->schema([
                                TextInput::make('valor_minimo')
                                    ->label('Valor Mínimo')
                                    ->columnSpan(2)
                                    ->prefix('R$')
                                    ->mask(RawJs::make('$money($input, \',\', \'.\')'))
                                    ->stripCharacters('.')
                                    ->required(),
                                TextInput::make('valor_venda')
                                    ->label('Valor de Venda')
                                    ->columnSpan(2)
                                    ->prefix('R$')
                                    ->mask(RawJs::make('$money($input, \',\', \'.\')'))
                                    ->stripCharacters('.')
                                    ->required(),
                            ]),
                        Tab::make('Cargas')
                            ->schema([
                                Repeater::make('volumes')
                                    ->columnSpanFull()
                                    ->collapsed()
                                    ->defaultItems(0)
                                    ->grid(4)
                                    ->required()
                                    ->columns(3)
                                    ->schema([
                                        TextInput::make('descricao')
                                            ->label('Descrição')
                                            ->columnSpanFull()
                                            ->required(),
                                        TextInput::make('altura')
                                            ->integer()
                                            ->required(),
                                        TextInput::make('largura')
                                            ->integer()
                                            ->required(),
                                        TextInput::make('comprimento')
                                            ->integer()
                                            ->label('Comp.')
                                            ->required(),
                                        TextInput::make('peso')
                                            ->label('Peso')
                                            ->columnSpanFull()
                                            ->integer()
                                            ->required(),
                                    ])
                                    ->itemLabel(fn (array $state): ?string => ($state['descricao']) ?? null),
                            ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->extraHeaderAttributes(['style' => 'width: 200px']),
                TextColumn::make('descricao'),
                TextColumn::make('tempo_de_producao')
                    ->label('Tempo de Produção')
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => DateHelper::fromSecondsToTime($state))
                    ->extraHeaderAttributes(['style' => 'width: 1px']),
                TextColumn::make('volumes_count')
                    ->label('Volumes')
                    ->alignCenter()
                    ->extraHeaderAttributes(['style' => 'width: 1px']),
                TextColumn::make('valor_venda')
                    ->label('R$ Un.')
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => TextHelper::toFormatedMoney($state))
                    ->extraHeaderAttributes(['style' => 'width: 1px']),
            ])
            ->actionsPosition(Tables\Enums\ActionsPosition::BeforeColumns)
            ->actions([]);
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
