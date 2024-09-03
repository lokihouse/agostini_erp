<?php

namespace App\Filament\Clusters\Sistema\Resources;

use App\Filament\Actions\Form\UserAlterarSenha;
use App\Filament\Clusters\Sistema;
use App\Filament\Clusters\Sistema\Resources\UsuarioResource\Pages;
use App\Filament\Clusters\Sistema\Resources\UsuarioResource\RelationManagers;
use App\Models\User;
use App\Models\Usuario;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UsuarioResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $label = 'Usuário';
    protected static ?string $pluralLabel = 'Usuários';
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Cadastros';
    protected static ?int $navigationSort = 2;
    protected static ?string $cluster = Sistema::class;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(12)
            ->schema([
                Group::make([
                    Select::make('empresa_id')
                        ->label('Empresa')
                        ->relationship('empresa', 'nome_fantasia')
                        ->preload()
                        ->required()
                        ->searchable()
                        ->columnSpan(3),
                    Select::make('roles')
                        ->label('Função')
                        ->relationship('roles', 'name')
                        ->preload()
                        ->required()
                        ->searchable()
                        ->columnSpan(3),
                ])
                    ->columns(12)
                    ->columnSpanFull(),
                Group::make([
                    TextInput::make('name')
                        ->label('Nome')
                        ->required()
                        ->columnSpan(4),
                    TextInput::make('username')
                        ->required()
                        ->columnSpan(2),
                    TextInput::make('password')
                        ->label('Senha')
                        ->visibleOn('create')
                        ->password()
                        ->revealable()
                        ->columnSpan(3),
                    Actions::make([
                        UserAlterarSenha::make('alterarSenha')
                    ])
                        ->verticalAlignment(VerticalAlignment::End)
                        ->visibleOn('edit')
                        ->fullWidth()
                        ->columnSpan(2),
                ])
                    ->columns(12)
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')
            ->columns([
                Tables\Columns\IconColumn::make('active')
                    ->label('')
                    ->boolean()
                    ->extraHeaderAttributes([
                        'style' => 'width: 50px',
                    ]),
                TextColumn::make('empresa.nome_fantasia')
                    ->extraHeaderAttributes(['style' => 'width: 200px']),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('username')
                    ->label('Usuário')
                    ->extraHeaderAttributes(['style' => 'width: 1px']),
                TextColumn::make('roles.name')
                    ->label('Função')
                    ->badge()
                    ->extraHeaderAttributes(['style' => 'width: 1px']),
            ])
            ->actionsPosition(Tables\Enums\ActionsPosition::BeforeColumns)
            ->actions([]);
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
