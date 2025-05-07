<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HolidayResource\Pages;
use App\Models\Holiday;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class HolidayResource extends Resource
{
    protected static ?string $model = Holiday::class;

    protected static ?string $modelLabel = 'Evento do Calendário';
    protected static ?string $pluralModelLabel = 'Calendário';

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'R.H.';
    protected static ?int $navigationSort = 30;

    public static function getHolidayTypeOptions(): array
    {
        return [
            'national' => 'Feriado Nacional',
            'state' => 'Feriado Estadual',
            'municipal' => 'Feriado Municipal',
            'optional_point' => 'Ponto Facultativo',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('company_id')
                    ->relationship('company', 'name')
                    ->label('Empresa') // Rótulo pode ser simplificado
                    // O helperText e a opcionalidade são mais relevantes para a edição
                    ->helperText('Associe a uma empresa ou deixe em branco para um evento global (visível na edição).')
                    ->searchable()
                    ->preload()
                    ->columnSpanFull()
                    ->hiddenOn('create'), // <-- OCULTA O CAMPO NA CRIAÇÃO

                Forms\Components\TextInput::make('name')
                    ->label('Nome do Evento')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('date')
                    ->label('Data')
                    ->required()
                    ->displayFormat('d/m/Y'),
                Forms\Components\Select::make('type')
                    ->label('Tipo')
                    ->options(self::getHolidayTypeOptions())
                    ->required(),
                Forms\Components\Toggle::make('is_recurrent')
                    ->label('Repete Anualmente?')
                    ->default(true)
                    ->helperText('Marque se este evento ocorre na mesma data todos os anos.'),
                Forms\Components\Textarea::make('notes')
                    ->label('Observações')
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::getHolidayTypeOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'national' => 'danger',
                        'state' => 'warning',
                        'municipal' => 'info',
                        'optional_point' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_recurrent')
                    ->label('Recorrente')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('company_id')
                    ->relationship('company', 'name')
                    ->label('Empresa')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(self::getHolidayTypeOptions()),
                Tables\Filters\TernaryFilter::make('is_recurrent')
                    ->label('Recorrente')
                    ->boolean()
                    ->trueLabel('Sim')
                    ->falseLabel('Não')
                    ->native(false),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(fn (Holiday $record): bool => $record->is_global),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (Holiday $record): bool => $record->is_global),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Nenhum evento encontrado')
            ->emptyStateDescription('Crie um evento para começar.');
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
            'index' => Pages\ListHolidays::route('/'),
            'create' => Pages\CreateHoliday::route('/create'),
            'edit' => Pages\EditHoliday::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        $query = parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);

        if ($user && $user->company_id) {
            return $query->where(function (Builder $subQuery) use ($user) {
                $subQuery->whereNull('company_id')
                    ->orWhere('company_id', $user->company_id);
            });
        } elseif ($user && $user->hasRole(config('filament-shield.super_admin.name'))) {
            if(is_null($user->company_id)) {
                return $query; // Super admin sem empresa vê todos
            }
            // Super admin com empresa, aplica a mesma lógica do usuário comum com empresa
            return $query->where(function (Builder $subQuery) use ($user) {
                $subQuery->whereNull('company_id')
                    ->orWhere('company_id', $user->company_id);
            });
        }

        return $query->whereRaw('1 = 0'); // Não mostra nada por padrão se não se encaixar
    }
}
