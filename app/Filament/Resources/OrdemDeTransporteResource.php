<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdemDeTransporteResource\Pages;
use App\Forms\Components\CargasMapaRotaFormField;
use App\Forms\Components\OrdemDeTransporteEntregasResumoField;
use App\Models\Cliente;
use App\Models\OrdemDeTransporte;
use App\Models\Produto;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrdemDeTransporteResource extends Resource
{
    protected static ?string $model = OrdemDeTransporte::class;
    protected static ?string $navigationGroup = 'Cargas';
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?int $navigationSort = 61;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Motorista & Veículo')
                        ->schema([
                            Select::make('user_id')
                                ->label('Motorista')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->relationship('motorista', 'nome'),
                            TextInput::make('placa_caminhao')
                                ->label('Placa do Caminhão')
                                ->mask('aaa-9*99')
                                ->required(),
                        ]),
                    Wizard\Step::make('Clientes & Produtos')
                        ->schema([
                            Repeater::make('entregas')
                                ->relationship()
                                ->label('')
                                ->addActionLabel('Adicionar Visita')
                                ->defaultItems(0)
                                ->columnSpanFull()
                                ->collapsible()
                                ->collapsed()
                                /*->schema([
                                    Group::make([
                                        Select::make('cliente_id')
                                            ->columnSpan(3)
                                            ->preload()
                                            ->required()
                                            ->searchable()
                                            ->live(onBlur: true)
                                            ->relationship('cliente', 'nome_fantasia'),
                                        Forms\Components\Section::make('Produtos')
                                            ->compact()
                                            ->headerActions([
                                                Forms\Components\Actions\Action::make('adicionarProduto')
                                                    ->size('sm')
                                                    ->modalWidth('xs')
                                                    ->form([
                                                        TextInput::make('quantidade')
                                                            ->label('Quantidade')
                                                            ->numeric(),
                                                        Select::make('produto_id')
                                                            ->options(Produto::query()->pluck('nome', 'id'))
                                                            ->preload()
                                                            ->searchable(),
                                                    ])
                                                ->action(function (Forms\Get $get, Forms\Set $set, $data){
                                                    $produtos = $get('produtos');
                                                    $produtos[] = $data;
                                                    $set('produtos', $produtos);
                                                })
                                            ])
                                            ->schema([
                                                OrdemDeTransporteEntregasResumoField::make('produtos')
                                                    ->label('')
                                                    ->live()
                                                    ->registerListeners([
                                                        'myComponent::updated' => [
                                                            function (OrdemDeTransporteEntregasResumoField $component, $value): void {
                                                                $produtos = $component->getState();
                                                                unset($produtos[intval($value)]);
                                                                $component->state($produtos);
                                                            }
                                                        ]
                                                    ])
                                            ])
                                            ->columnSpan(9),
                                    ])->columns(12)
                                ])
                                ->itemLabel(function (array $state): ?string{
                                    if($state['cliente']){
                                        return Cliente::query()->find($state['cliente'])->toArray()['nome_fantasia'];
                                    }
                                    return "";
                                })*/
                            ,

                        ]),
                    /*Wizard\Step::make('Documentos Auxiliares')
                        ->schema([
                            // ...
                        ]),*/
                    Wizard\Step::make('Carga & Rota')
                        ->schema([
                            /*CargasMapaRotaFormField::make('rota')
                                ->columnSpan(2)*/
                        ]),
                ])
                    ->startOnStep(2)
                    ->columnSpanFull()
                    /*->visible($form->getOperation() === 'create')*/,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
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
            'index' => Pages\ListOrdemDeTransportes::route('/'),
            'create' => Pages\CreateOrdemDeTransporte::route('/create'),
            'edit' => Pages\EditOrdemDeTransporte::route('/{record}/edit'),
        ];
    }
}
