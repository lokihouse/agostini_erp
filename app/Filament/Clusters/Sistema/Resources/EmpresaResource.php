<?php

namespace App\Filament\Clusters\Sistema\Resources;

use App\Filament\Actions\Form\EmpresaLocalizarCep;
use App\Filament\Actions\Form\EmpresaLocalizarCnpj;
use App\Filament\Clusters\Sistema;
use App\Filament\Clusters\Sistema\Resources\EmpresaResource\Pages;
use App\Filament\Clusters\Sistema\Resources\EmpresaResource\RelationManagers;
use App\Forms\Components\EmpresaRecursosHumanosMapa;
use App\Models\Empresa;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmpresaResource extends Resource
{
    protected static ?string $model = Empresa::class;
    protected static ?string $cluster = Sistema::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Cadastros';
    protected static ?string $label = 'Empresa';
    protected static ?string $pluralLabel = 'Empresas';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    // ->activeTab(2)
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Cadastro')
                            ->schema([
                                Group::make([
                                    TextInput::make('cnpj')
                                        ->label('CNPJ')
                                        ->columnSpan([ 'default' => 1, 'md' => 3 ])
                                        ->placeholder('00.000.000/0000-00')
                                        ->mask('99.999.999/9999-99')
                                        ->required()
                                        ->suffixAction(EmpresaLocalizarCnpj::make('localizarCnpj')),
                                    TextInput::make('razao_social')
                                        ->label('Razão Social')
                                        ->columnSpan([ 'default' => 1, 'md' => 5 ])
                                        ->required(),
                                    TextInput::make('nome_fantasia')
                                        ->label('Nome Fantasia')
                                        ->columnSpan([ 'default' => 1, 'md' => 4 ])
                                        ->required(),
                                ])
                                    ->columns([ 'default' => 1, 'md' => 12 ]),
                                Group::make([
                                    TextInput::make('email')
                                        ->label('Email')
                                        ->placeholder('contato@empresa.com.br')
                                        ->columnSpan([ 'default' => 1, 'md' => 3 ]),
                                    TextInput::make('telefone')
                                        ->label('Telefone')
                                        ->placeholder('(00) 00000-0000')
                                        ->mask('(99) 99999-9999')
                                        ->columnSpan([ 'default' => 1, 'md' => 2 ])
                                ])
                                    ->columns([ 'default' => 1, 'md' => 12 ]),
                                Group::make([
                                    TextInput::make('cep')
                                        ->label('CEP')
                                        ->columnSpan([ 'default' => 4, 'md' => 2 ])
                                        ->placeholder('00000-000')
                                        ->mask('99999-999')
                                        ->suffixAction(EmpresaLocalizarCep::make('localizarCep'))
                                        ->required(),
                                    TextInput::make('logradouro')
                                        ->label('Logradouro')
                                        ->columnSpan([ 'default' => 4, 'md' => 7 ])
                                        ->afterStateUpdated(fn($state) => $this->updateLocation())
                                        ->required(),
                                    TextInput::make('numero')
                                        ->label('Número')
                                        ->columnSpan([ 'default' => 2, 'md' => 1 ])
                                        ->required(),
                                    TextInput::make('complemento')
                                        ->label('Complemento')
                                        ->columnSpan([ 'default' => 2, 'md' => 2 ])
                                ])
                                    ->columns([ 'default' => 4, 'md' => 12 ]),
                                Group::make([
                                    TextInput::make('bairro')
                                        ->label('Bairro')
                                        ->columnSpan([ 'default' => 1, 'md' => 2 ])
                                        ->required(),
                                    TextInput::make('municipio')
                                        ->label('Município')
                                        ->columnSpan([ 'default' => 1, 'md' => 3 ])
                                        ->required(),
                                    Select::make('uf')
                                        ->label('Estado')
                                        ->columnSpan([ 'default' => 1, 'md' => 3 ])
                                        ->native(false)
                                        ->options([
                                            "AC" => "Acre",
                                            "AL" => "Alagoas",
                                            "AP" => "Amapá",
                                            "AM" => "Amazonas",
                                            "BA" => "Bahia",
                                            "CE" => "Ceará",
                                            "DF" => "Distrito Federal",
                                            "ES" => "Espirito Santo",
                                            "GO" => "Goiás",
                                            "MA" => "Maranhão",
                                            "MT" => "Mato Grosso",
                                            "MS" => "Mato Grosso do Sul",
                                            "MG" => "Minas Gerais",
                                            "PA" => "Para",
                                            "PB" => "Paraíba",
                                            "PR" => "Paraná",
                                            "PE" => "Pernambuco",
                                            "PI" => "Piauí",
                                            "RJ" => "Rio de Janeiro",
                                            "RN" => "Rio Grande do Norte",
                                            "RS" => "Rio Grande do Sul",
                                            "RO" => "Rondônia",
                                            "RR" => "Roraima",
                                            "SC" => "Santa Catarina",
                                            "SP" => "São Paulo",
                                            "SE" => "Sergipe",
                                            "TO" => "Tocantins"
                                        ])
                                        ->required()
                                ])
                                    ->columns([ 'default' => 1, 'md' => 12 ]),
                            ]),
                        /*Tabs\Tab::make('Produção')
                            ->schema([
                                Group::make([])->columns([ 'default' => 1, 'md' => 12 ])
                            ]),
                        Tabs\Tab::make('Vendas')
                            ->schema([
                                Group::make([])->columns([ 'default' => 1, 'md' => 12 ])
                            ]),
                        Tabs\Tab::make('Financeiro')
                            ->schema([
                                Group::make([])->columns([ 'default' => 1, 'md' => 12 ])
                            ]),
                        Tabs\Tab::make('Recursos Humanos')
                            ->visibleOn('edit')
                            ->columns([ 'default' => 1, 'md' => 12 ])
                            ->schema([
                                Group::make([
                                    EmpresaRecursosHumanosMapa::make('cerca_geografica_mapa')
                                        ->label('Cerca Geográfica')
                                        ->columnSpanFull(),
                                    TextInput::make('raio_cerca')
                                        ->label('Raio da Cerca')
                                        ->suffix('m')
                                        ->numeric()
                                        ->columnSpan(1),
                                    Actions::make([
                                        Action::make('update')
                                            ->label('Atualizar Cerca')
                                            ->action(function(Set $set, $state){
                                                $set('cerca_geografica_mapa', [
                                                    'latitude' => $state['latitude'],
                                                    'longitude' => $state['longitude'],
                                                    'raio' => $state['raio_cerca']
                                                ]);
                                            })
                                    ])
                                        ->verticalAlignment(VerticalAlignment::End)
                                        ->fullWidth()
                                ])
                                    ->columns([ 'default' => 1, 'md' => 2 ])
                                    ->columnSpan([ 'default' => 1, 'md' => 3 ])
                            ]),
                        Tabs\Tab::make('Carga')
                            ->schema([
                                Group::make([])->columns([ 'default' => 1, 'md' => 12 ])
                            ]),*/
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('nome_fantasia','asc')
            ->columns([
                Tables\Columns\IconColumn::make('active')
                    ->label('')
                    ->boolean()
                    ->extraHeaderAttributes([
                        'style' => 'width: 50px',
                    ]),
                TextColumn::make('cnpj')
                    ->searchable()
                    ->extraHeaderAttributes([
                        'style' => 'width: 180px',
                    ]),
                TextColumn::make('nome_fantasia')->searchable(),
                TextColumn::make('razao_social')->searchable(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListEmpresas::route('/'),
            'create' => Pages\CreateEmpresa::route('/create'),
            'edit' => Pages\EditEmpresa::route('/{record}/edit'),
        ];
    }
}
