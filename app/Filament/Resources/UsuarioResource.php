<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UsuarioResource\Pages;
use App\Filament\Resources\UsuarioResource\RelationManagers;
use App\Models\User;
use App\Utils\Cpf;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;

use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsuarioResource extends ResourceBase
{
    protected static ?string $model = User::class;
    protected static ?string $label = 'Usuário';
    protected static ?string $pluralLabel = 'Usuários';
    protected static ?string $navigationGroup = 'Sistema';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(20)
            ->schema([
                ToggleButtons::make('ativo')
                    ->boolean()
                    ->grouped()
                    ->columnSpan(4),
                Select::make('empresa_id')
                    ->relationship('empresa', 'nome_fantasia')
                    ->required()
                    ->preload()
                    ->searchable()
                    ->columnSpan(6),
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('ativo')
                    ->boolean()
                    ->width(1),
                TextColumn::make('empresa.nome_fantasia')
                    ->width(300),
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
            ])
            ->filters([
                SelectFilter::make('empresa_id')
                    ->relationship('empresa', 'nome_fantasia')
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
            'index' => Pages\ListUsuarios::route('/'),
            'create' => Pages\CreateUsuario::route('/create'),
            'edit' => Pages\EditUsuario::route('/{record}/edit'),
        ];
    }
}
