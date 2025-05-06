<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Usuário'; // Tradução do Model
    protected static ?string $pluralModelLabel = 'Usuários'; // Tradução do Model (Plural)

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Sistema';
    protected static ?string $navigationLabel = 'Usuários'; // Tradução para o menu
    protected static ?int $navigationSort = 1; // Ordem no menu (opcional)


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->label('Empresa') // Tradução
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->label('Nome Completo') // Tradução
                    ->required()
                    ->maxLength(255),
                TextInput::make('username')
                    ->label('Usuário (Login)') // Tradução
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('Senha') // Tradução
                    ->password()
                    ->required(fn(string $context): bool => $context === 'create')
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->rule(Password::defaults())
                    ->helperText(fn(string $context): string => $context === 'edit' ? 'Deixe em branco para não alterar a senha.' : ''), // Helper text
                Toggle::make('is_active')
                    ->label('Ativo?') // Tradução
                    ->required()
                    ->default(true),
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->searchable()
                    ->label('Funções')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome Completo') // Tradução
                    ->searchable()
                    ->sortable(),
                TextColumn::make('username')
                    ->label('Usuário (Login)') // Tradução
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.name') // Exibe os nomes das funções
                ->label('Funções') // Tradução
                ->badge() // Mostra como badges/tags
                ->searchable(), // Pode ser um pouco complexo para pesquisar em múltiplos valores, mas é possível
                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Criado em') // Tradução
                    ->dateTime('d/m/Y H:i') // Formato de data
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Atualizado em') // Tradução
                    ->dateTime('d/m/Y H:i') // Formato de data
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('company')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Empresa'),
                SelectFilter::make('roles') // Filtro por Função
                ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->label('Função'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Ativo')
                    ->boolean()
                    ->trueLabel('Ativos')
                    ->falseLabel('Inativos')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Editar'), // Tradução
                Tables\Actions\DeleteAction::make()->label('Excluir'), // Tradução
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Excluir Selecionados'), // Tradução
                ]),
            ])
            ->emptyStateHeading('Nenhum usuário encontrado') // Tradução
            ->emptyStateDescription('Crie um usuário para começar.'); // Tradução
    }

    public static function getRelations(): array
    {
        return [
        ];
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
