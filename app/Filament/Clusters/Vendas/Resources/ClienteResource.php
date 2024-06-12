<?php

namespace App\Filament\Clusters\Vendas\Resources;

use App\Filament\Clusters\Vendas;
use App\Filament\Clusters\Vendas\Resources\ClienteResource\Pages;
use App\Filament\ResourceBase;
use App\Forms\Components\VendedoresPorClienteField;
use App\Models\Cliente;
use App\Utils\TextFormater;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;
use Filament\Notifications\Notification;

class ClienteResource extends ResourceBase
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Vendas::class;
    public static function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Tabs::make('Tabs')
                    ->activeTab(3)
                    ->tabs([
                        Tabs\Tab::make('Cadastro')
                            ->schema([
                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('cnpj')
                                        ->label('CNPJ')
                                        ->columnSpan(2)
                                        ->placeholder('00.000.000/0000-00')
                                        ->mask('99.999.999/9999-99')
                                        ->suffixAction(
                                            Action::make('receitaWsFind')
                                                ->icon('heroicon-m-magnifying-glass')
                                                ->action(function (Set $set, $state) {
                                                    $state = TextFormater::clear($state);
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
                                                    $set('telefone', TextFormater::clear($response->json()['telefone']));
                                                    $set('cep', $response->json()['cep']);
                                                    $set('logradouro', $response->json()['logradouro']);
                                                    $set('complemento', $response->json()['complemento']);
                                                    $set('bairro', $response->json()['bairro']);
                                                    $set('municipio', $response->json()['municipio']);
                                                    $set('uf', $response->json()['uf']);
                                                })
                                        )
                                        ->required(),
                                ])->columns(10)
                                    ->columnSpanFull(),
                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('razao_social')
                                        ->label('Razão Social')
                                        ->columnSpan(3)
                                        ->required(),
                                    Forms\Components\TextInput::make('nome_fantasia')
                                        ->label('Nome Fantasia')
                                        ->columnSpan(3)
                                        ->required(),
                                    Forms\Components\TextInput::make('email')
                                        ->label('Email de contato')
                                        ->columnSpan(2),
                                    Forms\Components\TextInput::make('telefone')
                                        ->mask(RawJs::make(<<<'JS'
                                            (($input[2] === '9' && $input.length === 11) || $input[5] === '9') ? '(99) 9 9999-9999' : '(99) 9999-9999'
                                        JS))
                                        ->label('Telefone de contato')
                                        ->columnSpan(2),
                                ])->columns(10)
                                    ->columnSpanFull(),
                            ]),
                        Tabs\Tab::make('Endereço')
                            ->schema([
                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('cep')
                                        ->label('CEP')
                                        ->columnSpan(2)
                                        ->placeholder('00000-000')
                                        ->mask('99999-999')
                                        ->suffixAction(
                                            Action::make('viaCepFind')
                                                ->icon('heroicon-m-magnifying-glass')
                                                ->action(function (Set $set, $state) {
                                                    $state = TextFormater::clear($state);
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
                                    Forms\Components\TextInput::make('latitude')
                                        ->label('Latitude')
                                        ->disabled()
                                        ->columnStart(8)
                                        ->columnSpan(1)
                                        ->required(),
                                    Forms\Components\TextInput::make('longitude')
                                        ->label('Longitude')
                                        ->disabled()
                                        ->columnSpan(1)
                                        ->required(),
                                    Actions::make([
                                        Action::make('updateLocalizacao')
                                            ->label('Atualizar')
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
                                        ->fullWidth()
                                ])
                                    ->columns(10)
                                    ->columnSpanFull(),
                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('logradouro')
                                        ->label('Logradouro')
                                        ->columnSpan(3)
                                        ->required(),
                                    Forms\Components\TextInput::make('numero')
                                        ->label('Número')
                                        ->columnSpan(1)
                                        ->required(),
                                    Forms\Components\TextInput::make('complemento')
                                        ->label('Complemento')
                                        ->columnSpan(1),
                                    Forms\Components\TextInput::make('bairro')
                                        ->label('Bairro')
                                        ->columnSpan(2)
                                        ->required(),
                                    Forms\Components\TextInput::make('municipio')
                                        ->label('Município')
                                        ->columnSpan(2)
                                        ->required(),
                                    Forms\Components\TextInput::make('uf')
                                        ->label('UF')
                                        ->columnSpan(1)
                                        ->required(),
                                    Map::make('localizacao')
                                        ->label('Localização')
                                        ->columnSpanFull(),
                                ])
                                    ->columns(10)
                                    ->columnSpanFull(),
                            ]),
                        Tabs\Tab::make('Vendas')
                            ->columns(10)
                            ->schema([
                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('recorrencia_de_visitas_dias')
                                        ->label('Recorrência de visitas (dias)')
                                        ->columnSpanFull()
                                        ->numeric()
                                        ->required(),
                                ])
                                    ->columnSpan(2),
                                Forms\Components\Group::make([
                                    VendedoresPorClienteField::make('vendedores')
                                        ->label('Vendedores')
                                        ->columnSpanFull(),
                                ])
                                    ->hidden($form->getOperation() === 'create')
                                ->columnSpan(4)
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->defaultSort('razao_social', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('cnpj')
                    ->searchable()
                    ->extraHeaderAttributes(['style' => 'width: 200px'])
                    ->formatStateUsing(fn (string $state): string => "" . TextFormater::toCnpj($state)),
                Tables\Columns\TextColumn::make('razao_social')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nome_fantasia')
                    ->searchable(),
                Tables\Columns\TextColumn::make('recorrencia_de_visitas_dias')
                    ->label('Recorrência')
                    ->extraHeaderAttributes(['style' => 'width: 10px'])
                    ->formatStateUsing(fn (int $state): string => "{$state} dias"),
                Tables\Columns\TextColumn::make('proxima_visita')
                    ->label('Próxima visita')
                    ->extraHeaderAttributes(['style' => 'width: 10px'])
                    ->badge()
                    ->color(function (string $state): string {
                        if(strtotime($state) < strtotime('now')) return 'danger';
                        elseif (strtotime($state) < strtotime('+7 days')) return 'warning';
                        else return 'success';
                    })
                    ->formatStateUsing(fn (string $state): string => "" . TextFormater::toDate($state)),
        ]);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'edit' => Pages\EditCliente::route('/{record}/edit'),
        ];
    }
}
