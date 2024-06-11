<?php

namespace App\Filament\Clusters\Cadastros\Resources;

use App\Filament\Clusters\Cadastros;
use App\Filament\Clusters\Cadastros\Resources\ProdutoResource\Pages;
use App\Filament\Clusters\Cadastros\Resources\ProdutoResource\RelationManagers;
use App\Filament\ResourceBase;
use App\Models\Produto;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProdutoResource extends ResourceBase
{
    protected static ?string $model = Produto::class;
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Cadastros::class;

    public static function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Cadastro')
                            ->schema([
                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('nome')
                                        ->label('Nome')
                                        ->columnSpan(2)
                                        ->required(),
                                    Forms\Components\MarkdownEditor::make('descricao')
                                        ->label('Descrição')
                                        ->columnSpan(8),
                                ])
                            ]),
                        Tabs\Tab::make('Produção'),
                        Tabs\Tab::make('Financeiro'),
                        Tabs\Tab::make('Vendas')
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->defaultSort('nome','asc')
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable()
                    ->extraHeaderAttributes(['style' => 'width: 200px']),
                Tables\Columns\TextColumn::make('descricao')
                    ->limit(90)
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
