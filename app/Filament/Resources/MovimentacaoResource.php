<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovimentacaoResource\Pages;
use App\Filament\Resources\MovimentacaoResource\RelationManagers;
use App\Models\Movimentacao;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;

class MovimentacaoResource extends Resource
{
    protected static ?string $model = Movimentacao::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Financeiro';
    protected static ?int $navigationSort = 31;
    protected static ?string $navigationLabel = 'Movimentações';
    protected static ?string $label = 'Movimentação';
    protected static ?string $pluralLabel = 'Movimentações';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(12)
            ->schema([
                SelectTree::make('plano_de_conta_id')
                    ->label('Plano de Conta')
                    ->columnSpan(3)
                    ->enableBranchNode()
                    ->searchable()
                    ->withCount()
                    ->relationship('planoDeConta', 'nome', 'plano_de_conta_id'),
                Select::make('tipo')
                    ->label('Tipo de Movimentação')
                    ->options([
                        'entrada' => 'Entrada',
                        'saida' => 'Saída',
                    ])
                    ->columnSpan(2)
                    ->searchable()
                    ->required(),
                DatePicker::make('data_movimentacao')
                    ->label('Data da Movimentação')
                    ->columnSpan(2)
                    ->maxDate(now()->format('Y-m-d'))
                    ->required(),
                TextInput::make('descricao')
                    ->label('Descrição')
                    ->columnSpan(5)
                    ->columnStart(1)
                    ->nullable(),
                MoneyInput::make('valor')
                    ->label('Valor')
                    ->columnSpan(2)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('data_movimentacao')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->width(1),
                TextColumn::make('planoDeConta.nome')
                    ->label('Plano de Conta')
                    ->width(150),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->width(1),
                TextColumn::make('valor')
                    ->label('Valor')
                    ->alignEnd()
                    ->width(1),
                TextColumn::make('descricao')->label('Descrição'),


            ])
            ->defaultSort('data_movimentacao');
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
            'index' => Pages\ListMovimentacaos::route('/'),
            'create' => Pages\CreateMovimentacao::route('/create'),
            'edit' => Pages\EditMovimentacao::route('/{record}/edit'),
        ];
    }
}
