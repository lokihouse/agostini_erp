<?php

namespace App\Filament\Clusters\RecursosHumanos\Resources;

use App\Filament\Clusters\RecursosHumanos;
use App\Filament\Clusters\RecursosHumanos\Resources\RegistroDePontoResource\Pages;
use App\Filament\Clusters\RecursosHumanos\Resources\RegistroDePontoResource\RelationManagers;
use App\Models\RegistroDePonto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RegistroDePontoResource extends Resource
{
    protected static ?string $model = RegistroDePonto::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = RecursosHumanos::class;

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
            'index' => Pages\ListRegistroDePontos::route('/'),
            'create' => Pages\CreateRegistroDePonto::route('/create'),
            'edit' => Pages\EditRegistroDePonto::route('/{record}/edit'),
        ];
    }
}
