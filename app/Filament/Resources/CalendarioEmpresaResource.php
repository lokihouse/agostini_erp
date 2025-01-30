<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CalendarioEmpresaResource\Pages;
use App\Filament\Resources\CalendarioEmpresaResource\RelationManagers;
use App\Models\Calendario;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CalendarioEmpresaResource extends ResourceBase
{
    protected static ?string $model = Calendario::class;
    protected static ?string $navigationGroup = 'Recursos Humanos';
    protected static ?int $navigationSort = 50;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('data'),
                Select::make('tipo')
                    ->options([
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
                TextColumn::make('nome')
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
            'index' => Pages\ListCalendarioEmpresas::route('/'),
            'create' => Pages\CreateCalendarioEmpresa::route('/create'),
            'edit' => Pages\EditCalendarioEmpresa::route('/{record}/edit'),
        ];
    }
}
