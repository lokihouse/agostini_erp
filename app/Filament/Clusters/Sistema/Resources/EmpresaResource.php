<?php

namespace App\Filament\Clusters\Sistema\Resources;

use App\Filament\Clusters\Sistema;
use App\Filament\Clusters\Sistema\Resources\EmpresaResource\Pages\CreateEmpresa;
use App\Filament\Clusters\Sistema\Resources\EmpresaResource\Pages\EditEmpresa;
use App\Filament\Clusters\Sistema\Resources\EmpresaResource\Pages\ListEmpresas;
use App\Filament\ResourceBase;
use App\Forms\Components\EmpresaRecursosHumanosHorarioField;
use App\Models\Empresa;
use App\Utils\MyTextFormater;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;

class EmpresaResource extends ResourceBase
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
        return parent::form($form)->schema([
            Tabs::make('empresa')
                ->columnSpanFull()
                ->tabs([
                    Tabs\Tab::make('Cadastro')
                        ->schema([
                            Group::make([
                                TextInput::make('cnpj')
                                    ->label('CNPJ')
                                    ->columnSpan(3)
                                    ->placeholder('00.000.000/0000-00')
                                    ->mask('99.999.999/9999-99')
                                    ->required()
                                    ->suffixAction(
                                        Action::make('receitaWsFind')
                                            ->icon('heroicon-m-magnifying-glass')
                                            ->action(function (Set $set, $state) {
                                                $state = MyTextFormater::clear($state);
                                                if(empty($state)) return;
                                                $response = Http::get("https://receitaws.com.br/v1/cnpj/{$state}");

                                                if($response->json()['status'] === 'ERROR'){
                                                    Notification::make()
                                                        ->title('CNPJ não encontrado')
                                                        ->danger()
                                                        ->send();
                                                    return;
                                                }

                                                $set('razao_social', $response->json()['nome']);
                                                $set('nome_fantasia', $response->json()['fantasia']);
                                                $set('email', $response->json()['email']);
                                                $set('telefone', MyTextFormater::toTelefone(MyTextFormater::clear($response->json()['telefone'])));
                                                $set('cep', $response->json()['cep']);
                                                $set('logradouro', $response->json()['logradouro']);
                                                $set('complemento', $response->json()['complemento']);
                                                $set('bairro', $response->json()['bairro']);
                                                $set('municipio', $response->json()['municipio']);
                                                $set('uf', $response->json()['uf']);
                                            })
                                    ),
                            ])->columns(18)->columnSpanFull(),
                            Group::make([
                                TextInput::make('razao_social')
                                    ->label('Razão Social')
                                    ->columnSpan(6)
                                    ->required(),
                                TextInput::make('nome_fantasia')
                                    ->label('Nome Fantasia')
                                    ->columnSpan(6)
                                    ->required(),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->columnSpan(4),
                                TextInput::make('telefone')
                                    ->label('Telefone')
                                    ->placeholder('(00) 00000-0000')
                                    ->mask('(99) 99999-9999')
                                    ->columnSpan(2)
                            ])->columns(18)->columnSpanFull(),
                            Group::make([
                                TextInput::make('cep')
                                    ->label('CEP')
                                    ->columnSpan(3)
                                    ->placeholder('00000-000')
                                    ->mask('99999-999')
                                    ->suffixAction(
                                        Action::make('viaCepFind')
                                            ->icon('heroicon-m-magnifying-glass')
                                            ->action(function (Set $set, $state) {
                                                $state = MyTextFormater::clear($state);
                                                $response = Http::get("https://viacep.com.br/ws/{$state}/json/");

                                                if(isset($response->json()['erro']) && $response->json()['erro']){
                                                    Notification::make()
                                                        ->title('CEP não encontrado')
                                                        ->danger()
                                                        ->send();
                                                    return;
                                                }

                                                $set('logradouro', $response->json()['logradouro']);
                                                $set('numero', null);
                                                $set('complemento', $response->json()['complemento']);
                                                $set('bairro', $response->json()['bairro']);
                                                $set('municipio', $response->json()['localidade']);
                                                $set('uf', $response->json()['uf']);
                                            })
                                    )
                                    ->required(),
                                TextInput::make('logradouro')
                                    ->label('Logradouro')
                                    ->columnSpan(5)
                                    ->required(),
                                TextInput::make('numero')
                                    ->label('Núm.')
                                    ->columnSpan(1)
                                    ->required(),
                                TextInput::make('complemento')
                                    ->label('Compl.')
                                    ->columnSpan(2),
                                TextInput::make('bairro')
                                    ->label('Bairro')
                                    ->columnSpan(3)
                                    ->required(),
                                TextInput::make('municipio')
                                    ->label('Município')
                                    ->columnSpan(3)
                                    ->required(),
                                TextInput::make('uf')
                                    ->label('UF')
                                    ->mask('aa')
                                    ->columnSpan(1)
                                    ->required(),
                            ])->columns(18)->columnSpanFull(),
                            Group::make([
                                Map::make('localizacao')
                                    ->label('Localização')
                                    ->mapControls([
                                        'mapTypeControl'    => false,
                                        'scaleControl'      => false,
                                        'streetViewControl' => false,
                                        'rotateControl'     => false,
                                        'fullscreenControl' => false,
                                        'searchBoxControl'  => false, // creates geocomplete field inside map
                                        'zoomControl'       => false,
                                    ])
                                    ->height('220px')
                                    ->draggable(false)
                                    ->columnSpan(16),
                                Group::make([
                                    Actions::make([
                                        Action::make('updateLocalizacao')
                                            ->label('Localizar')
                                            ->action(function (Set $set, $state) {
                                                $response = Http::get("https://maps.googleapis.com/maps/api/geocode/json", [
                                                    'address' => "{$state['logradouro']}, {$state['numero']}, {$state['bairro']}, {$state['municipio']}, {$state['uf']}",
                                                    'key' => env('GOOGLE_MAPS_API_KEY'),
                                                ]);

                                                $lat = $response->json()['results'][0]['geometry']['location']['lat'];
                                                $lng = $response->json()['results'][0]['geometry']['location']['lng'];

                                                $set('latitude', $lat);
                                                $set('longitude', $lng);

                                                $set('localizacao', [
                                                    'lat' => floatval($lat),
                                                    'lng' => floatVal($lng),
                                                ]);
                                            }),
                                        ])
                                        ->extraAttributes(['style' => 'padding-top: 2rem;'])
                                        ->fullWidth(),
                                    TextInput::make('latitude')
                                        ->label('Latitude')
                                        ->required()
                                        ->disabled(),
                                    TextInput::make('longitude')
                                        ->label('Longitude')
                                        ->required()
                                        ->disabled()
                                ])->columnSpan(2),
                            ])->columns(18)->columnSpanFull()
                        ]),
                    Tabs\Tab::make('Recursos Humanos')
                        ->schema([
                            Group::make([
                                Repeater::make('horarios')
                                    ->columnSpan(8)
                                    ->defaultItems(0)
                                    ->collapsible()
                                    ->collapsed()
                                    ->itemLabel(function (array $state): ?string {
                                        if(empty($state['dia_da_semana']) || empty($state['inicio']) || empty($state['final'])) return null;
                                        return (implode(', ',array_values($state['dia_da_semana'])) . ' das ' . $state['inicio'] . ' às ' . $state['final']);
                                    })
                                    ->schema([
                                        Group::make([
                                            TimePicker::make('inicio')->live()->seconds(false),
                                            TimePicker::make('final')->live()->seconds(false),
                                        ])->columns(2),
                                        CheckboxList::make('dia_da_semana')
                                            ->columns(4)
                                            ->live()
                                            ->options([
                                                'domingo' => 'Domingo',
                                                'segunda' => 'Segunda',
                                                'terca' => 'Terça',
                                                'quarta' => 'Quarta',
                                                'quinta' => 'Quinta',
                                                'sexta' => 'Sexta',
                                                'sabado' => 'Sábado',
                                            ])
                                    ]),
                                Group::make([
                                    TextInput::make('tolerancia_turno')
                                        ->label('Tolerância Turno')
                                        ->numeric()
                                        ->suffix('min')
                                        ->mask('9999'),
                                    TextInput::make('tolerancia_jornada')
                                        ->label('Tolerância Jornada')
                                        ->numeric()
                                        ->suffix('min')
                                        ->mask('9999'),
                                    TextInput::make('raio_cerca')
                                        ->label('Raio da Cerca')
                                        ->numeric()
                                        ->suffix('m')
                                        ->mask('9999'),
                                    TextInput::make('justificativa_dias')
                                        ->label('Dias para Justificativa')
                                        ->numeric()
                                        ->mask('9999')
                                ])->columns(2)->columnSpan(4)
                            ])->columns(12)->columnSpanFull(),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->defaultSort('nome_fantasia', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('nome_fantasia')
                    ->searchable()
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmpresas::route('/'),
            'create' => CreateEmpresa::route('/create'),
            'edit' => EditEmpresa::route('/{record}/edit'),
        ];
    }
}
