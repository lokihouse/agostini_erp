<?php

namespace App\Filament\Resources;

use App\Filament\Exports\UserExporter;
use App\Filament\Imports\UserImporter;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\WorkShift;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Usuário';
    protected static ?string $pluralModelLabel = 'Usuários';

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Sistema';
    protected static ?string $navigationLabel = 'Usuários';
    protected static ?int $navigationSort = 12;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->label('Empresa')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live() // Adicionado para reatividade
                    ->afterStateUpdated(fn (callable $set) => $set('work_shift_id', null)), // Limpa work_shift_id se a empresa mudar

                Select::make('work_shift_id')
                    ->label('Jornada de Trabalho')
                    ->options(function (Get $get): array {
                        $companyId = $get('company_id');
                        if (!$companyId) {
                            return [];
                        }
                        return WorkShift::where('company_id', $companyId)->pluck('name', 'uuid')->all();
                    })
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->hidden(fn (Get $get) => !$get('company_id')) // Oculta se nenhuma empresa estiver selecionada
                    ->helperText('Selecione uma empresa primeiro.'),

                TextInput::make('name')
                    ->label('Nome Completo')
                    ->required()
                    ->maxLength(255),
                TextInput::make('username')
                    ->label('Usuário (Login)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->required(fn(string $context): bool => $context === 'create')
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->rule(Password::defaults())
                    ->helperText(fn(string $context): string => $context === 'edit' ? 'Deixe em branco para não alterar a senha.' : ''),
                Toggle::make('is_active')
                    ->label('Ativo?')
                    ->required()
                    ->default(true),
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple() // Permitir múltiplas roles
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
                    ->label('Nome Completo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('username')
                    ->label('Usuário (Login)')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('workShift.name') // Adicionada coluna da jornada
                ->label('Jornada')
                    ->placeholder('N/A')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Funções')
                    ->badge()
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('company')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Empresa'),
                SelectFilter::make('work_shift_id') // Adicionado filtro por jornada
                ->label('Jornada de Trabalho')
                    ->options(fn () => WorkShift::pluck('name', 'uuid')->all()) // Opções simples, pode ser melhorado com relationship
                    ->searchable()
                    ->preload(),
                SelectFilter::make('roles')
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
                Tables\Actions\EditAction::make()->label('Editar'),
                Tables\Actions\DeleteAction::make()->label('Excluir'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Excluir Selecionados'),
                ]),
            ])
            ->headerActions([
//                ExportAction::make()
//                    ->exporter(UserExporter::class),
//                ImportAction::make()
//                    ->importer(UserImporter::class),
            ])
            ->emptyStateHeading('Nenhum usuário encontrado')
            ->emptyStateDescription('Crie um usuário para começar.');
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}

