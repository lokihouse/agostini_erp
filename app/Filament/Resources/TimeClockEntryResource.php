<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TimeClockEntryResource\Pages;
use App\Models\TimeClockEntry;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Get;

class TimeClockEntryResource extends Resource
{
    protected static ?string $model = TimeClockEntry::class;

    protected static ?string $modelLabel = 'Batida de Ponto';
    protected static ?string $pluralModelLabel = 'Batidas de Ponto';

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';
    protected static ?string $navigationGroup = 'R.H.';
    protected static ?int $navigationSort = 55;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalhes da Batida')
                    ->columns(3) // Ajustado para 3 colunas para melhor acomodar o status
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Usuário')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $user = User::find($state);
                                if ($user) {
                                    $set('company_id', $user->company_id);
                                }
                            }),
                        Forms\Components\Select::make('company_id')
                            ->relationship('company', 'name')
                            ->label('Empresa')
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        Forms\Components\DateTimePicker::make('recorded_at')
                            ->label('Data e Hora da Batida')
                            ->required()
                            ->default(now()),
                        Forms\Components\Select::make('type')
                            ->label('Tipo de Batida')
                            ->options(TimeClockEntry::getEntryTypeOptions())
                            ->required(),
                        Forms\Components\Select::make('status') // CAMPO ADICIONADO
                        ->label('Status')
                            ->options(TimeClockEntry::getStatusOptions())
                            ->required()
                            ->default(TimeClockEntry::STATUS_NORMAL),
                    ]),
                Forms\Components\Section::make('Dados de Localização e Dispositivo')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\TextInput::make('ip_address')
                            ->label('Endereço IP')
                            ->disabled(fn(string $operation) => $operation === 'create')
                            ->dehydrated(false)
                            ->nullable(),
                        Forms\Components\Textarea::make('user_agent')
                            ->label('User Agent')
                            ->disabled(fn(string $operation) => $operation === 'create')
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->nullable(),
                    ]),
                Forms\Components\Section::make('Administrativo')
                    ->columns(2)
                    ->collapsible()
                    ->visible(fn (Get $get, string $operation) => $operation === 'edit' || !empty($get('notes')))
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Observações (Ex: ajuste manual)')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('approved_by')
                            ->relationship('approver', 'name')
                            ->label('Aprovado Por')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('approved_at')
                            ->label('Aprovado Em')
                            ->nullable(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuário')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('recorded_at')
                    ->label('Data/Hora Batida')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => TimeClockEntry::getEntryTypeOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'clock_in' => 'success',
                        'clock_out' => 'danger',
                        'start_break' => 'warning',
                        'end_break' => 'info',
                        'manual_entry' => 'gray',
                        default => 'primary',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('status') // COLUNA ADICIONADA
                ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (TimeClockEntry $record): string => $record->status_label)
                    ->color(fn (string $state): string => match ($state) {
                        TimeClockEntry::STATUS_NORMAL => 'gray',
                        TimeClockEntry::STATUS_ALERT => 'warning',
                        TimeClockEntry::STATUS_JUSTIFIED => 'info',
                        TimeClockEntry::STATUS_APPROVED => 'success',
                        TimeClockEntry::STATUS_ACCOUNTED => 'primary',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('latitude')
                    ->label('Lat.')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('longitude')
                    ->label('Lon.')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Aprovado Por')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado Em (Sistema)')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Usuário')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo de Batida')
                    ->options(TimeClockEntry::getEntryTypeOptions()),
                Tables\Filters\SelectFilter::make('status') // FILTRO ADICIONADO
                ->label('Status da Batida')
                    ->options(TimeClockEntry::getStatusOptions()),
                Tables\Filters\Filter::make('recorded_at')
                    ->form([
                        Forms\Components\DatePicker::make('recorded_from')->label('Batida De'),
                        Forms\Components\DatePicker::make('recorded_until')->label('Batida Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['recorded_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('recorded_at', '>=', $date),
                            )
                            ->when(
                                $data['recorded_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('recorded_at', '<=', $date),
                            );
                    }),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                    Tables\Actions\ForceDeleteBulkAction::make(),
//                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('recorded_at', 'desc')
            ->emptyStateHeading('Nenhuma batida de ponto encontrada')
            ->emptyStateDescription('As batidas de ponto dos usuários aparecerão aqui.');
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
            'index' => Pages\ListTimeClockEntries::route('/'),
            'create' => Pages\CreateTimeClockEntry::route('/create'),
            'edit' => Pages\EditTimeClockEntry::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        $query = parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);

        if (!$user) {
            return $query->whereRaw('1 = 0'); // Nenhum usuário logado, não mostra nada
        }

        // Super Admin pode ver tudo se não tiver company_id, ou da sua empresa se tiver
        if ($user->hasRole(config('filament-shield.super_admin.name'))) {
            return $user->company_id ? $query->where('company_id', $user->company_id) : $query;
        }

        // Usuário comum só vê da sua empresa (se tiver uma)
        if ($user->company_id) {
            return $query->where('company_id', $user->company_id);
        }

        // Usuário sem empresa e não é super admin
        return $query->whereRaw('1 = 0');
    }
}
