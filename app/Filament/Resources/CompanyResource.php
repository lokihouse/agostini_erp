<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
// use Filament\Forms\Components\Grid; // Não será mais usado diretamente no schema principal
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
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
                Tabs::make('ClientTabs')
                    ->tabs([
                        Tabs\Tab::make('Dados Cadastrais')
                            ->icon('heroicon-o-identification')
                            ->columns(['default' => 3, 'lg' => 12])
                            ->schema([
                                TextInput::make('taxNumber')
                                    ->label('CNPJ')
                                    ->required()
                                    ->maxLength(20)
                                    ->mask('99.999.999/9999-99')
                                    ->live(onBlur: true)
                                    ->unique(ignoreRecord: true)
                                    ->suffixAction(
                                        Action::make('consultarCnpj')
                                            ->label('Consultar')
                                            // Dynamically set icon and disabled state
                                            ->icon(fn (Livewire $livewire) => $livewire->isLoadingCnpj ? 'heroicon-o-arrow-path fi-spin' : 'heroicon-o-magnifying-glass')
                                            ->disabled(fn (Livewire $livewire) => $livewire->isLoadingCnpj)
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
                                                // The Livewire component (CreateClient/EditClient) will set isLoadingCnpj
                                                $livewire->dispatch('fetchCnpjCompanyData', cnpj: $cleanedCnpj);
                                            })
                                            ->color('gray')
                                    )
                                    ->placeholder('12.345.678/9012-34')
                                    ->columnSpan(3),
                                TextInput::make('social_name')
                                    ->label('Razão Social')
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 3, 'lg' => 4]),
                                TextInput::make('name')
                                    ->label('Nome Fantasia')
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 3, 'lg' => 5]),
                                TextInput::make('state_registration')
                                    ->label('Inscrição Estadual')
                                    ->maxLength(20)
                                    ->rule(function (Get $get, $record) {
                                        $value = $get('state_registration');
                                        if (empty($value)) return null;
                                        $companyId = $record?->company_id ?? auth()->user()?->company_id;
                                        return Rule::unique('clients', 'state_registration')
                                            ->where('company_id', $companyId)
                                            ->ignore($record?->uuid, 'uuid');
                                    })
                                    ->columnSpan(['default' => 3, 'lg' => 4]),
                                TextInput::make('municipal_registration')
                                    ->label('Inscrição Municipal')
                                    ->maxLength(20)
                                    ->columnSpan(['default' => 3, 'lg' => 4])
                            ]),

                        Tabs\Tab::make('Endereço e Localização')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Group::make([
                                    TextInput::make('address_zip_code')
                                        ->label('CEP')
                                        ->mask('99999-999')
                                        ->maxLength(9)
                                        ->live(onBlur: true)
                                        ->suffixAction(
                                            Action::make('consultarCep')
                                                ->label('Buscar')
                                                // Dynamically set icon and disabled state
                                                ->icon(fn (Livewire $livewire) => $livewire->isLoadingCep ? 'heroicon-o-arrow-path fi-spin' : 'heroicon-o-magnifying-glass')
                                                ->disabled(fn (Livewire $livewire) => $livewire->isLoadingCep)
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
                                                    Notification::make()
                                                        ->title('Consultando o CEP')
                                                        ->info()
                                                        ->send();
                                                    $livewire->dispatch('fetchCompanyCepData', cep: $cleanedCep);
                                                })
                                                ->color('gray')
                                        // ->loadingIndicator() // Removed
                                        )
                                        ->placeholder('12345-678')
                                        ->columnSpan(1),
                                    TextInput::make('address_street')
                                        ->label('Logradouro')
                                        ->maxLength(255)
                                        ->columnSpanFull(),
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
                                        ->columnSpan(1),
                                    TextInput::make('address_state')
                                        ->label('UF')
                                        ->length(2)
                                        ->maxLength(2)
                                        ->columnSpan(1),
                                ])->columns(3),

                                Group::make([
                                    TextInput::make('latitude')
                                        ->numeric()
                                        ->label('Latitude')
                                        ->readOnly(), // Mantido readOnly
                                    TextInput::make('longitude')
                                        ->numeric()
                                        ->label('Longitude')
                                        ->readOnly(), // Mantido readOnly
                                    Map::make('map_visualization')
                                        ->label('Localização')
                                        ->columnSpanFull()
                                        ->height('400px')
                                        ->reactive()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function(Get $get, Set $set){
                                            $set('latitude', $get('map_visualization')["lat"]);
                                            $set('longitude', $get('map_visualization')["lng"]);
                                        })
                                        ->draggable(true)
                                        ->clickable(false)
                                        ->geolocate(false)
                                        ->defaultLocation(fn (Get $get): array => [
                                            (float)($get('latitude') ?? -23.550520),
                                            (float)($get('longitude') ?? -46.633308)
                                        ])
                                        ->defaultZoom(fn (Get $get): int => ($get('latitude') && $get('longitude')) ? 15 : 5)
                                        ->mapControls([
                                            'mapTypeControl'    => false,
                                            'scaleControl'      => false,
                                            'streetViewControl' => false,
                                            'rotateControl'     => false,
                                            'fullscreenControl' => false,
                                            'searchBoxControl'  => false,
                                            'zoomControl'       => false,
                                        ]),
                                ])->columns(2),
                            ])->columns(2),
                    ])->columnSpanFull(),
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

