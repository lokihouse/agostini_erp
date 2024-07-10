<?php

namespace App\Filament\Clusters\Cadastros\Resources;

use App\Filament\Clusters\Cadastros;
use App\Filament\Clusters\Cadastros\Resources\FuncionarioResource\Pages;
use App\Filament\Clusters\Cadastros\Resources\FuncionarioResource\RelationManagers;
use App\Models\User;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class FuncionarioResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $label = 'Funcionário';
    protected static ?string $pluralLabel = 'Funcionários';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 2;

    protected static ?string $cluster = Cadastros::class;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->where('empresa_id', auth()->user()->empresa_id);
        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make([
                    Select::make('roles')
                        ->label('Função')
                        ->relationship('roles', 'name', fn($query) => $query->where('name', '!=', 'super_admin'))
                        ->preload()
                        ->required()
                        ->searchable()
                        ->columnSpan(2),
                    TextInput::make('name')
                        ->label('Nome')
                        ->required()
                        ->columnSpan(4),
                    TextInput::make('username')
                        ->required()
                        ->columnSpan([
                            'default' => 6,
                            'xl' => 2,
                        ]),
                    TextInput::make('password')
                        ->label('Senha')
                        ->hidden($form->getOperation() !== 'create')
                        ->password()
                        ->revealable()
                        ->required()
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
                        ->extraAttributes(['style' => 'padding-top: 2rem;'])
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
        return $table
            ->columns([
                TextColumn::make('username')
                    ->extraHeaderAttributes(['style' => 'width: 1px']),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label('Função')
                    ->badge()
                    ->extraHeaderAttributes(['style' => 'width: 1px']),
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
            'create' => Pages\CreateFuncionarios::route('/create'),
            'edit' => Pages\EditFuncionarios::route('/{record}/edit'),
        ];
    }
}
