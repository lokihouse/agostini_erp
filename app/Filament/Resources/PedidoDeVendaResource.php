<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PedidoResource\Pages;
use App\Filament\Resources\PedidoResource\RelationManagers;
use App\Models\Pedido;
use App\Models\PedidoDeVenda;
use Filament\Forms;
use Filament\Forms\Form;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PedidoDeVendaResource extends ResourceBase
{
    protected static ?string $model = PedidoDeVenda::class;
    protected static ?string $navigationGroup = 'Vendas';
    protected static ?int $navigationSort = 31;
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
                    ->width(50),
                TextColumn::make('status')
                    ->width(100),
                TextColumn::make('cliente.nome_fantasia')
                    ->width(300),
                TextColumn::make('vendedor.nome'),
                TextColumn::make('total')
                    ->width(50),
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
            'index' => Pages\ListPedidosDeVenda::route('/'),
            'create' => Pages\CreatePedidoDeVenda::route('/create'),
            'edit' => Pages\EditPedidoDeVenda::route('/{record}/edit'),
        ];
    }
}
