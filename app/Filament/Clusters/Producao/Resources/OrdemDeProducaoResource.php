<?php

namespace App\Filament\Clusters\Producao\Resources;

use App\Filament\Clusters\Producao;
use App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource\Pages;
use App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource\RelationManagers;
use App\Models\OrdemDeProducao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdemDeProducaoResource extends Resource
{
    protected static ?string $model = OrdemDeProducao::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Producao::class;

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
            'index' => Pages\ListOrdemDeProducaos::route('/'),
            'create' => Pages\CreateOrdemDeProducao::route('/create'),
            'edit' => Pages\EditOrdemDeProducao::route('/{record}/edit'),
        ];
    }
}
