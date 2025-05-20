<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
// use Filament\Forms\Components\Grid; // Não será mais usado diretamente no schema principal
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

// Novas importações baseadas no ClientResource
use Filament\Forms\Components\Tabs;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Livewire\Component as Livewire; // Garanta que o alias Livewire está correto
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Illuminate\Validation\Rule;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $modelLabel = 'Empresa';
    protected static ?string $pluralModelLabel = 'Empresas';

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Sistema';
    protected static ?string $navigationLabel = 'Empresas';
    protected static ?int $navigationSort = 11;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('CompanyTabs')
                    ->tabs([
                        Tabs\Tab::make('Dados Cadastrais')
                            ->icon('heroicon-o-identification')
                            ->columns(4)
                            ->schema([
                                TextInput::make('taxNumber')
                                    ->label('CNPJ')
                                    ->required()
                                    ->mask('99.999.999/9999-99')
                                    ->live(onBlur: true) // Para consulta de CNPJ
                                    ->rule(function (Get $get, $record) {
                                        // Para Company, a unicidade é global, não por company_id
                                        return Rule::unique('companies', 'taxNumber')
                                            ->ignore($record?->uuid, 'uuid');
                                    })
                                    ->suffixAction(
                                        Action::make('consultarCnpjEmpresa')
                                            ->label('Consultar')
                                            ->icon(fn (Livewire $livewire) => property_exists($livewire, 'isLoadingCnpj') && $livewire->isLoadingCnpj ? 'heroicon-o-arrow-path fi-spin' : 'heroicon-o-magnifying-glass')
                                            ->disabled(fn (Livewire $livewire) => property_exists($livewire, 'isLoadingCnpj') && $livewire->isLoadingCnpj)
                                            ->action(function (Get $get, Livewire $livewire) {
                                                $cnpj = $get('taxNumber');
                                                if (empty($cnpj)) {
                                                    Notification::make()
                                                        ->title('CNPJ não informado')
                                                        ->body('Por favor, insira um CNPJ para consulta.')
                                                        ->warning()
                                                        ->send();
                                                    return;
                                                }
                                                $cleanedCnpj = preg_replace('/[^0-9]/', '', $cnpj);
                                                // A página CreateCompany/EditCompany precisará tratar este evento
                                                $livewire->dispatch('fetchCnpjDataCompany', cnpj: $cleanedCnpj);
                                            })
                                            ->color('gray')
                                    )
                                    ->placeholder('12.345.678/9012-34'),
                                TextInput::make('name')
                                    ->label('Nome Fantasia')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                TextInput::make('socialName')
                                    ->label('Razão Social')
                                    ->maxLength(255)
                                    ->columnSpanFull(), // Para ocupar a linha toda se estiver em grid de 1

                                TextInput::make('telephone')
                                    ->label('Telefone')
                                    ->tel()
                                    ->mask('(99) 9999-9999')
                                    ->dehydrateStateUsing(static fn (?string $state): ?string => $state ? preg_replace('/[^0-9]/', '', $state) : null)
                                    ->rule(static function () {
                                        return static function (string $attribute, $value, \Closure $fail) {
                                            if (empty($value)) {
                                                return;
                                            }
                                            $cleaned = preg_replace('/[^0-9]/', '', $value);
                                            if (!in_array(strlen($cleaned), [10, 11])) {
                                                $fail('O campo Telefone deve conter 10 ou 11 dígitos.');
                                            }
                                        };
                                    })
                                    ->columnSpanFull(),
                            ])->columns(1), // Ajuste o layout interno da aba se necessário

                        Tabs\Tab::make('Endereço e Localização')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Group::make() // Grupo para campos de endereço
                                ->schema([
                                    TextInput::make('address_zip_code')
                                        ->label('CEP')
                                        ->mask('99999-999')
                                        ->live(onBlur: true) // Para consulta de CEP
                                        ->dehydrateStateUsing(static fn (?string $state): ?string => $state ? preg_replace('/[^0-9]/', '', $state) : null)
                                        ->suffixAction(
                                            Action::make('consultarCepEmpresa')
                                                ->label('Buscar')
                                                ->icon(fn (Livewire $livewire) => property_exists($livewire, 'isLoadingCep') && $livewire->isLoadingCep ? 'heroicon-o-arrow-path fi-spin' : 'heroicon-o-magnifying-glass')
                                                ->disabled(fn (Livewire $livewire) => property_exists($livewire, 'isLoadingCep') && $livewire->isLoadingCep)
                                                ->action(function (Get $get, Livewire $livewire) {
                                                    $cep = $get('address_zip_code');
                                                    if (empty($cep)) {
                                                        Notification::make()
                                                            ->title('CEP não informado')
                                                            ->body('Por favor, insira um CEP para consulta.')
                                                            ->warning()
                                                            ->send();
                                                        return;
                                                    }
                                                    $cleanedCep = preg_replace('/[^0-9]/', '', $cep);
                                                    if (strlen($cleanedCep) !== 8) {
                                                        Notification::make()
                                                            ->title('CEP Inválido')
                                                            ->body('O CEP deve conter 8 dígitos.')
                                                            ->warning()
                                                            ->send();
                                                        return;
                                                    }
                                                    // A página CreateCompany/EditCompany precisará tratar este evento
                                                    $livewire->dispatch('fetchCepDataCompany', cep: $cleanedCep);
                                                })
                                                ->color('gray')
                                        )
                                        ->placeholder('12345-678')
                                        ->columnSpan(1), // Ocupa 1 de 3 colunas
                                    TextInput::make('address_street')
                                        ->label('Logradouro')
                                        ->maxLength(255)
                                        ->columnSpan(2), // Ocupa 2 de 3 colunas
                                    TextInput::make('address_number')
                                        ->label('Número')
                                        ->maxLength(50)
                                        ->columnSpan(1),
                                    TextInput::make('address_complement')
                                        ->label('Complemento')
                                        ->maxLength(100)
                                        ->columnSpan(1),
                                    TextInput::make('address_district')
                                        ->label('Bairro')
                                        ->maxLength(100)
                                        ->columnSpan(1),
                                    TextInput::make('address_city')
                                        ->label('Cidade')
                                        ->maxLength(100)
                                        ->columnSpan(2),
                                    TextInput::make('address_state')
                                        ->label('UF')
                                        ->maxLength(2)
                                        ->columnSpan(1),
                                ])->columns(3), // Define 3 colunas para o grupo de endereço

                                Group::make() // Grupo para coordenadas e mapa
                                ->schema([
                                    TextInput::make('latitude')
                                        ->label('Latitude')
                                        ->numeric()
                                        ->readOnly() // Será preenchido pela consulta de CEP/CNPJ ou mapa
                                        ->rules(['nullable', 'regex:/^[-]?(([0-8]?[0-9])\.(\d+)|90(\.0+)?)$/']),
                                    TextInput::make('longitude')
                                        ->label('Longitude')
                                        ->numeric()
                                        ->readOnly() // Será preenchido pela consulta de CEP/CNPJ ou mapa
                                        ->rules(['nullable', 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/']),
                                    Map::make('map_visualization')
                                        ->label('Localização no Mapa')
                                        ->columnSpanFull()
                                        ->height('400px')
                                        ->reactive() // Para atualizar com base na lat/lng
                                        ->draggable(true) // Permitir arrastar o marcador para ajustar
                                        ->clickable(true) // Permitir clicar no mapa para definir lat/lng
                                        ->geolocate(true) // Botão para geolocalização do usuário
                                        ->geolocateLabel('Usar minha localização')
                                        ->defaultLocation(fn (Get $get): array => [ // Posição inicial do mapa
                                            (float)($get('latitude') ?? -14.235004), // Centro do Brasil se não houver lat/lng
                                            (float)($get('longitude') ?? -51.92528)
                                        ])
                                        ->defaultZoom(fn (Get $get): int => ($get('latitude') && $get('longitude')) ? 16 : 4) // Zoom inicial
                                        ->mapControls([ // Controles do mapa
                                            'mapTypeControl'    => true,
                                            'scaleControl'      => true,
                                            'streetViewControl' => true,
                                            'rotateControl'     => true,
                                            'fullscreenControl' => true,
                                            'searchBoxControl'  => false, // Desabilitar busca no mapa, pois temos CEP/CNPJ
                                            'zoomControl'       => true,
                                        ])
                                        // Atualiza os campos de latitude e longitude quando o marcador do mapa é movido
                                        ->afterStateUpdated(function (Get $get, callable $set, $state) {
                                            if (isset($state['lat']) && isset($state['lng'])) {
                                                $set('latitude', $state['lat']);
                                                $set('longitude', $state['lng']);
                                            }
                                        })
                                        // Define a localização do marcador com base nos campos de latitude e longitude
                                        ->reverseGeocode([
                                            'street' => 'address_street',
                                            'city' => 'address_city',
                                            'state' => 'address_state',
                                            'zip' => 'address_zip_code',
                                            // Adicione outros campos se o pacote suportar e for útil
                                        ])
                                        ->drawingControl(false) // Desabilitar ferramentas de desenho
                                        ->autocomplete('address_street') // Tentar autocompletar o campo de rua
                                        ->autocompleteReverse(true) // Tentar geocodificação reversa ao mover o marcador
                                        ->columnSpanFull(),
                                ])->columns(2), // Define 2 colunas para o grupo de coordenadas e mapa
                            ])->columns(1), // A aba em si ocupa 1 coluna no layout principal de abas
                    ])->columnSpanFull(), // Faz com que o componente Tabs ocupe toda a largura
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome Fantasia')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('taxNumber')
                    ->label('CNPJ')
                    ->searchable()
                    ->formatStateUsing(function (?string $state): string {
                        if (empty($state)) {
                            return '';
                        }
                        $cleanedState = preg_replace('/[^0-9]/', '', $state);
                        if (strlen($cleanedState) === 14) {
                            return sprintf(
                                '%s.%s.%s/%s-%s',
                                substr($cleanedState, 0, 2),
                                substr($cleanedState, 2, 3),
                                substr($cleanedState, 5, 3),
                                substr($cleanedState, 8, 4),
                                substr($cleanedState, 12, 2)
                            );
                        }
                        return $state;
                    }),
                TextColumn::make('address_city')
                    ->label('Cidade')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('address_state')
                    ->label('UF')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('telephone')
                    ->label('Telefone')
                    ->searchable()
                    ->formatStateUsing(function (?string $state): string {
                        if (empty($state)) return '';
                        $cleaned = preg_replace('/[^0-9]/', '', $state);
                        if (strlen($cleaned) == 11) {
                            return sprintf('(%s) %s-%s', substr($cleaned, 0, 2), substr($cleaned, 2, 5), substr($cleaned, 7, 4));
                        } elseif (strlen($cleaned) == 10) {
                            return sprintf('(%s) %s-%s', substr($cleaned, 0, 2), substr($cleaned, 2, 4), substr($cleaned, 6, 4));
                        }
                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('socialName')
                    ->label('Razão Social')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('address_street')
                    ->label('Logradouro')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('address_district')
                    ->label('Bairro')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('latitude')
                    ->label('Latitude')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('longitude')
                    ->label('Longitude')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Mostrar Excluídos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Editar'),
                Tables\Actions\DeleteAction::make()->label('Excluir'),
                Tables\Actions\ForceDeleteAction::make()->label('Excluir Permanentemente'),
                Tables\Actions\RestoreAction::make()->label('Restaurar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Excluir Selecionados'),
                    Tables\Actions\ForceDeleteBulkAction::make()->label('Excluir Perm. Selecionados'),
                    Tables\Actions\RestoreBulkAction::make()->label('Restaurar Selecionados'),
                ]),
            ])
            ->emptyStateHeading('Nenhuma empresa encontrada')
            ->emptyStateDescription('Crie uma empresa para começar.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
