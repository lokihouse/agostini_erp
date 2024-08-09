<?php

namespace App\Filament\Clusters\Producao\Resources;

use App\Filament\Clusters\Producao;
use App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource\Pages;
use App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource\RelationManagers;
use App\Models\OrdemDeProducao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdemDeProducaoResource extends Resource
{
    protected static ?string $navigationLabel = 'Ordem de Produção';
    protected static ?string $pluralLabel = 'Ordens de Produção';
    protected static ?string $label = 'Ordem de Produção';
    protected static ?string $model = OrdemDeProducao::class;
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected static ?string $navigationGroup = 'Cadastros';
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
                TextColumn::make('codigo')
                    ->label('#'),
                TextColumn::make('status')->badge()
                    ->extraHeaderAttributes(['class' => 'w-1'])
                    ->alignment(Alignment::Center),
                TextColumn::make('data_inicio')
                    ->extraHeaderAttributes(['class' => 'w-1'])
                    ->alignment(Alignment::Center)
                    ->date('d/m/Y'),
                TextColumn::make('data_previsao')
                    ->extraHeaderAttributes(['class' => 'w-1'])
                    ->alignment(Alignment::Center)
                    ->date('d/m/Y'),
                TextColumn::make('data_final')
                    ->extraHeaderAttributes(['class' => 'w-1'])
                    ->alignment(Alignment::Center)
                    ->date('d/m/Y'),
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
