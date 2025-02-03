<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FuncionarioResource\Pages;
use App\Filament\Resources\FuncionarioResource\RelationManagers;
use App\Models\Funcionario;
use App\Models\User;
use App\Utils\Cpf;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;

use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;

class FuncionarioResource extends ResourceBase
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Cadastro';
    protected static ?int $navigationSort = 11;
    protected static ?string $label = 'Usuário';
    protected static ?string $pluralLabel = 'Usuários';
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('empresa_id', auth()->user()->empresa_id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('')
                    ->columnSpanFull()
                    ->schema([
                        Tabs\Tab::make('Cadastro')
                            ->columns(20)
                            ->schema([
                                ToggleButtons::make('ativo')
                                    ->boolean()
                                    ->grouped()
                                    ->columnSpan(4),
                                Select::make('roles')
                                    ->label('Função')
                                    ->relationship('roles', 'name')
                                    ->required()
                                    ->preload()
                                    ->searchable()
                                    ->columnSpan(4),
                                TextInput::make('cpf')
                                    ->mask('***.***.***-**')
                                    ->required()
                                    ->columnStart(1)
                                    ->columnSpan(3),
                                TextInput::make('nome')
                                    ->required()
                                    ->columnSpan(6),
                                TextInput::make('username')
                                    ->required()
                                    ->label('Nome de Usuário')
                                    ->columnSpan(4),
                                TextInput::make('password')
                                    ->password()
                                    ->required(fn() => $form->getOperation() === 'create')
                                    ->label('Senha')
                                    ->columnSpan(4),
                            ]),
                        Tabs\Tab::make('Vendas')
                            ->columns(20)
                            ->schema([
                                MoneyInput::make('meta_mensal_de_venda')
                                    ->columnSpan(3),
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('ativo')
                    ->boolean()
                    ->width(1),
                TextColumn::make('cpf')
                    ->label('CPF')
                    ->formatStateUsing(fn($state) => Cpf::format($state))
                    ->width(175),
                TextColumn::make('nome')
                    ->searchable()
                    ->label('Nome'),
                TextColumn::make('username')
                    ->searchable()
                    ->width(150),
                TextColumn::make('roles.name')
                    ->label('Função')
                    ->badge()
                    ->alignCenter()
                    ->width(1),
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
            'index' => Pages\ListFuncionarios::route('/'),
            'create' => Pages\CreateFuncionario::route('/create'),
            'edit' => Pages\EditFuncionario::route('/{record}/edit'),
        ];
    }
}
