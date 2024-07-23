<?php

namespace App\Filament\Clusters\Cargas\Resources;

use App\Filament\Clusters\Cargas;
use App\Filament\Clusters\Cargas\Resources\OrdemDeExpedicaoResource\Pages;
use App\Filament\Clusters\Cargas\Resources\OrdemDeExpedicaoResource\RelationManagers;
use App\Models\OrdemDeExpedicao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdemDeExpedicaoResource extends Resource
{
    protected static ?string $model = OrdemDeExpedicao::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Cargas::class;

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
            'index' => Pages\ListOrdemDeExpedicaos::route('/'),
            'create' => Pages\CreateOrdemDeExpedicao::route('/create'),
            'edit' => Pages\EditOrdemDeExpedicao::route('/{record}/edit'),
        ];
    }
}
