<?php

namespace App\Filament\Clusters\Cadastros\Resources;

use App\Filament\Actions\Form\UserAlterarSenha;
use App\Filament\Clusters\Cadastros;
use App\Filament\Clusters\Cadastros\Resources\FuncionarioResource\Pages;
use App\Filament\Clusters\Cadastros\Resources\FuncionarioResource\RelationManagers;
use App\Models\Funcionario;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class FuncionarioResource extends Resource
{
    protected static ?string $model = Funcionario::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $cluster = Cadastros::class;
    protected static ?string $label = 'Funcionário';
    protected static ?string $pluralLabel = 'Funcionários';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->columns(12)
            ->schema([
                Group::make([
                    Select::make('roles')
                        ->label('Função')
                        ->options(Role::query()->where('name', '!=', 'super_admin')->pluck('name', 'name'))
                        ->preload()
                        ->required()
                        ->searchable()
                        ->columnSpan(3),
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
                        ->columnSpan(3),
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
                IconColumn::make('active')
                    ->label('')
                    ->boolean()
                    ->extraHeaderAttributes([
                        'style' => 'width: 50px',
                    ]),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('username')
                    ->label('Usuário')
                    ->extraHeaderAttributes(['style' => 'width: 1px']),
                TextColumn::make('role')
                    ->label('Função')
                    ->badge()
                    ->extraHeaderAttributes(['style' => 'width: 1px']),
            ])
            ->actionsPosition(ActionsPosition::BeforeColumns)
            ->actions([]);
    }

    public static function getRelations(): array
    {
        return [
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
