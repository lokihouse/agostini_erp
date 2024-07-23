<?php

namespace App\Filament\Clusters\Financeiro\Resources;

use App\Filament\Clusters\Financeiro;
use App\Filament\Clusters\Financeiro\Resources\MovimentacaoFinanceiraResource\Pages;
use App\Filament\Clusters\Financeiro\Resources\MovimentacaoFinanceiraResource\RelationManagers;
use App\Models\MovimentacaoFinanceira;
use App\Utils\TextFormater;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MovimentacaoFinanceiraResource extends Resource
{
    protected static ?string $model = MovimentacaoFinanceira::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Financeiro::class;

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
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->extraHeaderAttributes(['class' => 'w-1'])
                    ->label('Registro')
                    ->date('d/m/Y H:i:s'),
                Tables\Columns\TextColumn::make('descricao'),
                Tables\Columns\TextColumn::make('planoDeConta')
                    ->formatStateUsing(fn ($state) => $state->codigo . ' - ' . $state->descricao),
                Tables\Columns\IconColumn::make('natureza')
                    ->extraHeaderAttributes(['class' => 'w-1'])
                    ->icon(fn (string $state): string => match ($state) {
                        'credito' => 'heroicon-o-plus-circle',
                        'debito' => 'heroicon-o-minus-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'credito' => 'success',
                        'debito' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('valor')
                    ->formatStateUsing(fn ($state) => TextFormater::toMoney($state))
                    ->extraHeaderAttributes(['class' => 'w-1']),
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
            'index' => Pages\ListMovimentacaoFinanceiras::route('/'),
            //'create' => Pages\CreateMovimentacaoFinanceira::route('/create'),
            //'edit' => Pages\EditMovimentacaoFinanceira::route('/{record}/edit'),
        ];
    }
}
