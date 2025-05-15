<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
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
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $modelLabel = 'Funcionário';
    protected static ?string $pluralModelLabel = 'Funcionários';
    protected static ?string $navigationGroup = 'Cadastros';
    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome Completo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Hidden::make('company_id')
                    ->default(fn () => Auth::user()->company_id),

                Forms\Components\Select::make('work_shift_id')
                    ->label('Jornada de Trabalho')
                    ->options(function (): array { // Não precisa de Get $get se company_id é fixo
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
                    ->nullable()
                    ->helperText('Jornadas da sua empresa.'),

                Forms\Components\Toggle::make('is_active')
                    ->label('Ativo?')
                    ->required()
                    ->default(true)
                    ->columnSpanFull(), // Ocupa a largura total se for o último na linha
                Forms\Components\TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->rule(Password::default())
                    ->helperText(fn(string $context): string => $context === 'edit' ? 'Deixe em branco para não alterar a senha.' : '')
                    ->maxLength(255),
                Forms\Components\TextInput::make('password_confirmation')
                    ->label('Confirmação da Senha')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(false),
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name') // Assumindo que a relação se chama 'roles' e o campo de nome é 'name'
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->label('Funções do Funcionário'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('workShift.name')
                    ->label('Jornada')
                    ->placeholder('N/A')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Funções')
                    ->badge()
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('work_shift_id')
                    ->label('Jornada de Trabalho')
                    ->options(function (): array {
                        $companyId = Auth::user()->company_id;
                        if (!$companyId) {
                            return [];
                        }
                        return WorkShift::where('company_id', $companyId)->pluck('name', 'uuid')->all();
                    })
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('roles')
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
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
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
        // Garante que is_active tenha um valor se não vier do form (embora o Toggle deva enviar)
        $data['is_active'] = $data['is_active'] ?? true;
        return $data;
    }

    // Se você precisar de lógica específica ao atualizar, pode usar mutateFormDataBeforeSave ou mutateFormDataBeforeUpdate
    // Exemplo:
    // protected static function mutateFormDataBeforeSave(array $data): array
    // {
    //     if (Auth::check() && Auth::user()->company_id) {
    //         // Garante que o company_id não seja alterado para um funcionário existente
    //         // (getEloquentQuery já restringe a edição a funcionários da empresa)
    //         $data['company_id'] = Auth::user()->company_id;
    //     }
    //     return $data;
    // }
}
