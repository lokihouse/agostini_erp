<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitaResource\Pages;
use App\Filament\Resources\VisitaResource\RelationManagers;
use App\Models\Visita;
use Filament\Forms\Form;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VisitaResource extends ResourceBase
{
    protected static ?string $model = Visita::class;
    protected static ?string $navigationGroup = 'Vendas';
    protected static ?int $navigationSort = 30;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('data')
                    ->width(300)
                    ->date('j \de F \de Y'),
                TextColumn::make('cliente.nome_fantasia'),
                TextColumn::make('vendedor.nome')->width(300),
                TextColumn::make('status')
                    ->width(1)
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        default => 'gray',
                        'em andamento' => 'warning',
                        'finalizada' => 'info',

                    })
            ])->recordUrl(function ($record) { return route('filament.app.pages.registro-de-visita', ['id' => $record->id]); });
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisitas::route('/'),
        ];
    }
}
