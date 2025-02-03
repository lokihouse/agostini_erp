<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdutoResource\Pages;
use App\Filament\Resources\ProdutoResource\RelationManagers;
use App\Models\Produto;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;

class ProdutoResource extends ResourceBase
{
    protected static ?string $model = Produto::class;
    protected static ?string $navigationGroup = 'Cadastro';
    protected static ?int $navigationSort = 12;
    protected static ?string $label = 'Produto';
    protected static ?string $pluralLabel = 'Produtos';
    protected static ?string $navigationIcon = 'heroicon-o-gift';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(4)
            ->schema([
                Group::make([
                    TextInput::make('nome')
                        ->label('Nome')
                        ->required()
                ]),
                Tabs::make('')
                    ->columnSpan(3)
                    ->schema([
                        Tabs\Tab::make('Vendas')
                            ->columns(4)
                            ->schema([
                                MoneyInput::make('valor_minimo_venda')
                                    ->columnSpan(1)
                                    ->label('Valor Mínimo de Venda')
                                    ->required(),
                                MoneyInput::make('valor_nominal_venda')
                                    ->columnSpan(1)
                                    ->label('Valor Nominal de Venda')
                                    ->required(),
                            ]),
                        // Tabs\Tab::make('Cargas')
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
            'index' => Pages\ListProdutos::route('/'),
            'create' => Pages\CreateProduto::route('/create'),
            'edit' => Pages\EditProduto::route('/{record}/edit'),
        ];
    }
}
