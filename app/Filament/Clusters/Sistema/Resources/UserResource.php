<?php

namespace App\Filament\Clusters\Sistema\Resources;

use App\Filament\Clusters\Sistema;
use App\Filament\Clusters\Sistema\Resources\UserResource\Pages;
use App\Filament\Clusters\Sistema\Resources\UserResource\RelationManagers;
use App\Filament\ResourceBase;
use App\Models\User;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends ResourceBase
{
    protected static ?string $model = User::class;
    protected static ?string $label = 'Usuário';
    protected static ?string $pluralLabel = 'Usuários';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Sistema::class;

    public static function form(Form $form): Form
    {
        return parent::form($form)->schema([
            Group::make([
                Select::make('empresa_id')
                    ->label('Empresa')
                    ->relationship('empresa', 'nome')
                    ->preload()
                    ->required()
                    ->searchable()
                    ->columnSpan(2),
                Select::make('roles')
                    ->label('Função')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->required()
                    ->searchable()
                    ->columnSpan(2),
            ])
                ->columns([
                    'default' => 2,
                    'xl' => 10,
                ])
                ->columnSpanFull(),
            Group::make([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->columnSpan(6),
                TextInput::make('username')
                    ->required()
                    ->columnSpan([
                        'default' => 6,
                        'xl' => 2,
                    ]),
                TextInput::make('password')
                    ->hidden($form->getOperation() !== 'create')
                    ->password()
                    ->revealable()
                    ->columnSpan([
                        'default' => 6,
                        'xl' => 2,
                    ]),
                Actions::make([
                    Action::make('resetStars')
                        ->modalSubmitActionLabel('Atualizar Senha')
                        ->modalWidth('xs')
                        ->label('Atualizar Senha')
                        ->form([
                            TextInput::make('password')
                                ->password()
                                ->revealable()
                                ->label('Nova Senha')
                                ->required(),
                        ])
                        ->action(function (array $data, User $record): void {
                            $record->password = Hash::make($data['password']);
                            $record->save();
                            Notification::make()
                                ->title('Senha atualizada com sucesso!')
                                ->success()
                                ->send();
                        })
                ])
                    ->hidden($form->getOperation() === 'create')
                    ->extraAttributes(['class' => 'user_reset_password_action'])
                    ->fullWidth()
                    ->columnSpan([
                        'default' => 6,
                        'xl' => 2,
                    ]),
            ])
                ->columns([
                    'default' => 6,
                    'xl' => 10,
                ])
                ->columnSpanFull()
        ]);
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->defaultSort('name', 'asc')
            ->columns([
                TextColumn::make('empresa.nome')
                    ->extraHeaderAttributes(['style' => 'width: 200px']),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('username')
                    ->extraHeaderAttributes(['style' => 'width: 1px']),
                TextColumn::make('roles.name')
                    ->label('Função')
                    ->badge()
                    ->extraHeaderAttributes(['style' => 'width: 1px']),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
