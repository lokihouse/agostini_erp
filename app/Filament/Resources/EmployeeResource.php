<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkShift; // Importar WorkShift
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get; // Importar Get para o campo work_shift_id
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $modelLabel = 'Funcionário';
    protected static ?string $pluralModelLabel = 'Funcionários';
    protected static ?string $navigationGroup = 'Cadastros';
    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(4)
            ->schema([
                Forms\Components\Hidden::make('company_id')
                    ->default(fn () => Auth::user()->company_id),
                Forms\Components\Hidden::make('user_id'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Ativo?')
                    ->required()
                    ->inline(false)
                    ->default(true),
                Forms\Components\TextInput::make('name')
                    ->label('Nome Completo')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('username')
                    ->label('Usuário (Login)')
                    ->required()
                    //->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\Select::make('work_shift_id')
                    ->label('Jornada de Trabalho')
                    ->options(function (): array {
                        $companyId = Auth::user()->company_id;
                        if (!$companyId) {
                            return [];
                        }
                        return WorkShift::where('company_id', $companyId)
                            ->pluck('name', 'uuid')
                            ->all();
                    })
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->rule(Password::default())
                    ->helperText(fn(string $context): string => $context === 'edit' ? 'Deixe em branco para não alterar a senha.' : '')
                    ->maxLength(255),
                Forms\Components\Select::make('roles')
                    ->options(function (): array {
                        return Role::whereNot('name', config('filament-shield.super_admin.name'))->pluck('name', 'name')->all();
                    })
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->label('Funções do Funcionário')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.workShift.name')
                    ->label('Jornada')
                    ->placeholder('N/A')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.roles.name')
                    ->label('Funções')
                    ->badge()
                    ->searchable(),
                Tables\Columns\IconColumn::make('user.is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->emptyStateHeading('Nenhum funcionário encontrado')
            ->emptyStateDescription('Crie um funcionário para começar.');
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        if (Auth::check() && Auth::user()->company_id) {
            $query->where('company_id', Auth::user()->company_id);
        } else {
            $query->whereRaw('1 = 0');
        }
        return $query;
    }

    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        if (Auth::check() && Auth::user()->company_id) {
            $data['company_id'] = Auth::user()->company_id;
        }
        $data['is_active'] = $data['is_active'] ?? true;
        return $data;
    }
}
