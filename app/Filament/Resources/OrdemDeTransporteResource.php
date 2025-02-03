<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdemDeTransporteResource\Pages;
use App\Filament\Resources\OrdemDeTransporteResource\RelationManagers;
use App\Models\OrdemDeTransporte;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdemDeTransporteResource extends Resource
{
    protected static ?string $model = OrdemDeTransporte::class;
    protected static ?string $navigationGroup = 'Cargas';
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?int $navigationSort = 61;

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
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListOrdemDeTransportes::route('/'),
            'create' => Pages\CreateOrdemDeTransporte::route('/create'),
            'edit' => Pages\EditOrdemDeTransporte::route('/{record}/edit'),
        ];
    }
}
