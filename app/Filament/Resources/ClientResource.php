<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Notifications\Notification;
use Livewire\Component as Livewire; // Ensure Livewire alias is correct
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Illuminate\Validation\Rule;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Cadastros';
    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'Cliente';

    protected static ?string $pluralModelLabel = 'Clientes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('ClientTabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Dados Cadastrais')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Forms\Components\TextInput::make('taxNumber')
                                    ->label('CNPJ')
                                    ->required()
                                    ->maxLength(20)
                                    ->mask('99.999.999/9999-99')
                                    ->live(onBlur: true)
                                    ->rule(function (Get $get, $record) {
                                        $companyId = $record?->company_id ?? auth()->user()?->company_id;
                                        // Basic check, model should handle if company_id is absolutely required for validation logic
                                        if (!$companyId && !$record) {
                                            // Potentially return a validation failure if companyId is indeterminable and critical for the unique rule context
                                        }
                                        return Rule::unique('clients', 'taxNumber')
                                            ->where('company_id', $companyId)
                                            ->ignore($record?->uuid, 'uuid');
                                    })
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('consultarCnpj')
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
                                                $livewire->dispatch('fetchCnpjClientData', cnpj: $cleanedCnpj);
                                            })
                                            ->color('gray')
                                    // ->loadingIndicator() // Removed as it's causing an error
                                    )
                                    ->placeholder('12.345.678/9012-34')
                                    ->columnSpan(3),
                                Forms\Components\TextInput::make('social_name')
                                    ->label('Razão Social')
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 3, 'lg' => 4]),
                                Forms\Components\TextInput::make('name')
                                    ->label('Nome Fantasia')
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 3, 'lg' => 5]),
                                Forms\Components\TextInput::make('state_registration')
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
                                Forms\Components\TextInput::make('municipal_registration')
                                    ->label('Inscrição Municipal')
                                    ->maxLength(20)
                                    ->columnSpan(['default' => 3, 'lg' => 4]),
                                Forms\Components\Select::make('status')
                                    ->options(Client::getStatusOptions())
                                    ->default(Client::STATUS_ACTIVE)
                                    ->columnSpan(['default' => 3, 'lg' => 4]),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->maxLength(255)
                                    ->rule(function (Get $get, $record) {
                                        $value = $get('email');
                                        if (empty($value)) return null;
                                        $companyId = $record?->company_id ?? auth()->user()?->company_id;
                                        return Rule::unique('clients', 'email')
                                            ->where('company_id', $companyId)
                                            ->ignore($record?->uuid, 'uuid');
                                    })
                                    ->columnSpan(['default' => 3, 'lg' => 4]),
                                Forms\Components\TextInput::make('phone_number')
                                    ->label('Telefone')
                                    ->tel()
                                    ->mask('(99) 99999-9999')
                                    ->maxLength(20)
                                    ->columnSpan(['default' => 3, 'lg' => 4]),
                                Forms\Components\TextInput::make('website')
                                    ->url()
                                    ->prefix("http://")
                                    ->maxLength(255)
                                    ->columnSpan(['default' => 3, 'lg' => 4]),
                                Forms\Components\Textarea::make('notes')
                                    ->label('Observações')
                                    ->columnSpanFull(),
                            ])->columns(['default' => 3, 'lg' => 12]),

                        Forms\Components\Tabs\Tab::make('Endereço e Localização')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('address_zip_code')
                                        ->label('CEP')
                                        ->mask('99999-999')
                                        ->maxLength(9)
                                        ->live(onBlur: true)
                                        ->suffixAction(
                                            Forms\Components\Actions\Action::make('consultarCep')
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
                                                    // The Livewire component (CreateClient/EditClient) will set isLoadingCep
                                                    $livewire->dispatch('fetchCepData', cep: $cleanedCep);
                                                })
                                                ->color('gray')
                                        // ->loadingIndicator() // Removed
                                        )
                                        ->placeholder('12345-678')
                                        ->columnSpan(1),
                                    Forms\Components\TextInput::make('address_street')
                                        ->label('Logradouro')
                                        ->maxLength(255)
                                        ->columnSpanFull(),
                                    Forms\Components\TextInput::make('address_number')
                                        ->label('Número')
                                        ->maxLength(50)
                                        ->columnSpan(1),
                                    Forms\Components\TextInput::make('address_complement')
                                        ->label('Complemento')
                                        ->maxLength(100)
                                        ->columnSpan(1),
                                    Forms\Components\TextInput::make('address_district')
                                        ->label('Bairro')
                                        ->maxLength(100)
                                        ->columnSpan(1),
                                    Forms\Components\TextInput::make('address_city')
                                        ->label('Cidade')
                                        ->maxLength(100)
                                        ->columnSpan(1),
                                    Forms\Components\TextInput::make('address_state')
                                        ->label('UF')
                                        ->length(2)
                                        ->maxLength(2)
                                        ->columnSpan(1),
                                ])->columns(3),

                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('latitude')
                                        ->numeric()
                                        ->label('Latitude')
                                        ->readOnly(), // Mantido readOnly
                                    Forms\Components\TextInput::make('longitude')
                                        ->numeric()
                                        ->label('Longitude')
                                        ->readOnly(), // Mantido readOnly
                                    Map::make('map_visualization')
                                        ->label('Localização')
                                        ->columnSpanFull()
                                        ->height('400px')
                                        ->reactive()
                                        ->draggable(false)
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
                Tables\Columns\TextColumn::make('social_name')
                    ->label('Razão Social')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome Fantasia')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('taxNumber')
                    ->label('CNPJ')
                    ->searchable()
                    ->formatStateUsing(fn (?string $state): string => $state ? preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $state) : ''),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Telefone'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Client::STATUS_ACTIVE => 'success',
                        Client::STATUS_INACTIVE => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => Client::getStatusOptions()[$state] ?? ucfirst($state)),
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(Client::getStatusOptions()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
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
