<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CalendarioResource\Pages;
use App\Filament\Resources\CalendarioResource\RelationManagers;
use App\Models\Calendario;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CalendarioResource extends ResourceBase
{
    protected static ?string $model = Calendario::class;
    protected static ?string $navigationGroup = 'Sistema';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('data'),
                Select::make('tipo')
                    ->options([
                        'nacional' => 'Feriado Nacional',
                        'estadual' => 'Feriado Estadual',
                        'municipal' => 'Feriado Municipal',
                        'recesso' => 'Recesso',
                        'folga' => 'Folga',
                    ]),
                TextInput::make('nome')->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('data', 'desc')
            ->columns([
                TextColumn::make('data')
                    ->width(1)
                    ->date('j \de F \de Y'),
                TextColumn::make('tipo')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'nacional' => 'Feriado Nacional',
                        'estadual' => 'Feriado Estadual',
                        'municipal' => 'Feriado Municipal',
                        'recesso' => 'Recesso',
                        'folga' => 'Folga',
                    })
                    ->width(1),
                TextColumn::make('nome'),
                TextColumn::make('empresa.nome_fantasia')
                    ->label('Empresa')
                    ->width(1)
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
            'index' => Pages\ListCalendarios::route('/'),
            'create' => Pages\CreateCalendario::route('/create'),
            'edit' => Pages\EditCalendario::route('/{record}/edit'),
        ];
    }
}
