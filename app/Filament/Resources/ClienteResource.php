<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Filament\Resources\ClienteResource\RelationManagers;
use App\Models\Cliente;
use App\Utils\Cep;
use App\Utils\Cnpj;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Closure;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;

class ClienteResource extends ResourceBase
{
    protected static ?string $model = Cliente::class;
    protected static ?string $navigationGroup = 'Cadastro';
    protected static ?int $navigationSort = 11;
    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static function getCoordinatesFromAddress($address) {
        $apiKey = env('GOOGLE_MAPS_API_KEY');
        $address = urlencode($address);
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$apiKey}";

        $response = file_get_contents($url);
        $responseData = json_decode($response, true);

        if ($responseData['status'] == 'OK') {
            $latitude = $responseData['results'][0]['geometry']['location']['lat'];
            $longitude = $responseData['results'][0]['geometry']['location']['lng'];
            return ['lat' => $latitude, 'lng' => $longitude];
        } else {
            return ['error' => $responseData['status']];
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(20)
            ->schema([
                Tabs::make('')
                    ->columnSpanFull()
                    ->schema([
                        Tabs\Tab::make('Cadastro')
                            ->columns(20)
                            ->schema([
                                TextInput::make('cnpj')
                                    ->required()
                                    ->label('CNPJ')
                                    ->mask("**.***.***/9999-99")
                                    ->columnSpan(4)
                                    ->rules([
                                        fn (): Closure => function (string $attribute, $value, Closure $fail) {
                                            $cleannedCNPJ = Cnpj::clear($value);
                                            $existeCliente = Cliente::query()->where("cnpj", $cleannedCNPJ)->exists();
                                            if ($existeCliente) {
                                                $fail('CNPJ já em uso.');
                                            }
                                        },
                                    ])
                                    ->suffixAction(
                                        Action::make('getCnpjInfos')
                                            ->icon('heroicon-o-magnifying-glass')
                                            ->action(function (Set $set, $state) {
                                                $cnpj = Cnpj::clear($state);
                                                $response = Http::get("https://publica.cnpj.ws/cnpj/{$cnpj}");

                                                if ($response->getStatusCode() !== 200) {
                                                    Notification::make('erroNoCnpjWs')
                                                        ->danger()
                                                        ->title('Erro ao tentar localizar CNPJ.')
                                                        ->body($response->json('detalhes'))
                                                        ->send();
                                                    return;
                                                }

                                                $cnpj = $response->json();

                                                $set('razao_social', $cnpj['razao_social']);
                                                $set('nome_fantasia', $cnpj['estabelecimento']['nome_fantasia']);

                                                $cnpj_estabelecimento = $cnpj['estabelecimento'];
                                                if(isset($cnpj_estabelecimento)){
                                                    $inscricao_estadual = $cnpj_estabelecimento['inscricoes_estaduais'];
                                                    $inscricao_estadual = count($inscricao_estadual) ? $inscricao_estadual[0]['inscricao_estadual'] : null;
                                                    $set('inscricao_estadual', $inscricao_estadual );

                                                    $telefone = $cnpj_estabelecimento['ddd1'] . $cnpj_estabelecimento['telefone1'];
                                                    $set('telefone', $telefone );

                                                    $set('endereco.logradouro', $cnpj_estabelecimento['logradouro'] );
                                                    $set('endereco.numero', $cnpj_estabelecimento['numero'] );
                                                    $set('endereco.complemento', $cnpj_estabelecimento['complemento'] );
                                                    $set('endereco.bairro', $cnpj_estabelecimento['bairro'] );
                                                    $set('endereco.cidade', $cnpj_estabelecimento['cidade']['nome'] );
                                                    $set('endereco.estado', $cnpj_estabelecimento['estado']['sigla'] );

                                                }
                                            })
                                    ),
                                TextInput::make('razao_social')
                                    ->label('Razão Social')
                                    ->required()
                                    ->columnSpan(8),
                                TextInput::make('nome_fantasia')
                                    ->label('Nome Fantasia')
                                    ->required()
                                    ->columnSpan(8),
                                TextInput::make('inscricao_estadual')
                                    ->label('Inscrição Estadual')
                                    ->columnSpan(5),
                                TextInput::make('inscricao_municipal')
                                    ->label('Inscrição Municipal')
                                    ->columnSpan(5),
                                TextInput::make('telefone')
                                    ->label('Telefone')
                                    ->mask("(99) 9 9999-9999")
                                    ->columnSpan(5),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->columnSpan(5),
                            ]),
                        Tabs\Tab::make('Endereço')
                            ->columns(['default' => 3])
                            ->schema([
                                Group::make([
                                    TextInput::make('endereco.cep')
                                        ->label('CEP')
                                        ->mask('99999-999')
                                        ->suffixAction(
                                            Action::make('getCepInfos')
                                                ->icon('heroicon-o-magnifying-glass')
                                                ->action(function (Set $set, $state) {
                                                    $cep = Cep::clear($state);
                                                    $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

                                                    if ($response->getStatusCode() !== 200) {
                                                        Notification::make('erroNoCnpjWs')
                                                            ->danger()
                                                            ->title('Erro ao tentar localizar CEP.')
                                                            ->body($response->json('detalhes'))
                                                            ->send();
                                                        return;
                                                    }

                                                    $cep = $response->json();

                                                    $set('endereco.logradouro', $cep['logradouro'] );
                                                    $set('endereco.numero', $cep['unidade'] );
                                                    $set('endereco.complemento', $cep['complemento'] );
                                                    $set('endereco.bairro', $cep['bairro'] );
                                                    $set('endereco.cidade', $cep['localidade'] );
                                                    $set('endereco.estado', $cep['uf'] );
                                                })
                                        )
                                        ->columnSpan(['default' => 3, 'sm' => 1]),
                                    TextInput::make('endereco.logradouro')
                                        ->label('Logradouro')
                                        ->columnSpan(['default' => 3, 'sm' => 4]),
                                    TextInput::make('endereco.numero')
                                        ->label('Nº')
                                        ->columnSpan(1),
                                    TextInput::make('endereco.complemento')
                                        ->label('Complemento')
                                        ->columnSpan(2),
                                    TextInput::make('endereco.bairro')
                                        ->label('Bairro')
                                        ->columnSpan(['default' => 3, 'sm' => 2]),
                                    TextInput::make('endereco.cidade')
                                        ->label('Cidade')
                                        ->columnSpan(['default' => 3, 'sm' => 2]),
                                    TextInput::make('endereco.estado')
                                        ->label('Estado')
                                        ->columnSpan(2),
                                    Actions::make([
                                        Action::make('localizar')
                                            ->action(function (Set $set, $state) {
                                                $address = $state['endereco']['logradouro'] . ' ' . $state['endereco']['numero'] . ' ' . $state['endereco']['bairro'] . ' ' . $state['endereco']['cidade'] . ' ' . $state['endereco']['estado'];
                                                $coords = ClienteResource::getCoordinatesFromAddress($address);
                                                $set('localizacao', $coords);
                                            })
                                    ])->fullWidth()->verticallyAlignEnd()
                                ])
                                    ->columns(['default' => 3, 'sm' => 5])
                                    ->columnSpan(['default' => 3, 'sm' => 2]),

                                Map::make('localizacao')
                                    ->draggable(false)
                                    ->mapControls([
                                        'mapTypeControl'    => false,
                                        'scaleControl'      => false,
                                        'streetViewControl' => false,
                                        'rotateControl'     => false,
                                        'fullscreenControl' => false,
                                        'searchBoxControl'  => false,
                                        'zoomControl'       => false,
                                    ])
                                    ->columnSpan(['default' => 3, 'sm' => 1]),
                            ]),
                        Tabs\Tab::make('Vendas')
                            ->schema([
                                Select::make('user_id')
                                    ->relationship('vendedor', 'nome')
                                    ->required()
                            ])
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cnpj')
                    ->formatStateUsing(fn($state) => Cnpj::format($state))
                    ->searchable()
                    ->width(1),
                TextColumn::make('nome_fantasia')
                    ->searchable()
                    ->width(300),
                TextColumn::make('razao_social')
                    ->searchable(),
                TextColumn::make('vendedor.nome')
                    ->width(300)
                    ->searchable()
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
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'edit' => Pages\EditCliente::route('/{record}/edit'),
        ];
    }
}
