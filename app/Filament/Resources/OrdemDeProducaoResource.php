<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdemDeProducaoResource\Pages;
use App\Filament\Resources\OrdemDeProducaoResource\RelationManagers;
use App\Models\OrdemDeProducao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdemDeProducaoResource extends Resource
{
    protected static ?string $model = OrdemDeProducao::class;
    protected static ?string $navigationGroup = 'Produção';
    protected static ?string $label = 'Ordem de Produção';
    protected static ?string $pluralLabel = 'Ordens de Produção';
    protected static ?int $navigationSort = 20;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->width(1),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('cliente.nome')
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
            'index' => Pages\ListOrdemDeProducaos::route('/'),
            'create' => Pages\CreateOrdemDeProducao::route('/create'),
            'edit' => Pages\EditOrdemDeProducao::route('/{record}/edit'),
        ];
    }
}
