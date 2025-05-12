<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PauseReasonResource\Pages;
use App\Models\PauseReason;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Validation\Rule;

class PauseReasonResource extends Resource
{
    protected static ?string $model = PauseReason::class;

    protected static ?string $navigationIcon = 'heroicon-o-pause-circle';
    protected static ?string $modelLabel = 'Motivo de Pausa';
    protected static ?string $pluralModelLabel = 'Motivos de Pausa';
    protected static ?string $navigationGroup = 'Sistema';
    protected static ?int $navigationSort = 13;

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole(config('filament-shield.super_admin.name'));

        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nome do Motivo')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->rules([
                        'required',
                        'max:255',
                        function ($get, $record) use ($isSuperAdmin) {
                            return Rule::unique('pause_reasons', 'name')
                                ->where(function ($query) use ($get, $isSuperAdmin) {
                                    $companyId = $get('company_id');
                                    if ($isSuperAdmin && is_null($companyId)) {
                                        // Para super admin criando/editando motivo global, company_id é NULL
                                        $query->whereNull('company_id');
                                    } elseif ($companyId) {
                                        // Para motivo específico de empresa
                                        $query->where('company_id', $companyId);
                                    } else {
                                        // Usuário não super admin, sempre valida contra sua empresa
                                        $query->where('company_id', Auth::user()->company_id);
                                    }
                                })
                                ->ignore($record?->uuid, 'uuid');
                        }
                    ]),
                Select::make('type')
                    ->label('Tipo de Pausa')
                    ->options(PauseReason::getTypeOptions())
                    ->required(),
                Toggle::make('is_active')
                    ->label('Ativo')
                    ->default(true)
                    ->required(),
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->label('Empresa (Opcional)')
                    ->helperText('Deixe em branco para um motivo global (visível a todas as empresas).')
                    ->searchable()
                    ->preload()
                    ->visible($isSuperAdmin) // Visível apenas para Super Admin
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->label('Observações')
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome do Motivo')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => PauseReason::getTypeOptions()[$state] ?? $state)
                    ->colors([
                        'success' => PauseReason::TYPE_PRODUCTIVE_TIME,
                        'danger' => PauseReason::TYPE_DEAD_TIME,
                        'warning' => PauseReason::TYPE_MANDATORY_BREAK,
                    ]),
                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->placeholder('Global')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(PauseReason::getTypeOptions()),
                TernaryFilter::make('is_active')
                    ->label('Status Ativo')
                    ->boolean()
                    ->trueLabel('Sim')
                    ->falseLabel('Não')
                    ->native(false),
                SelectFilter::make('company_id')
                    ->label('Empresa')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => Auth::user()->hasRole(config('filament-shield.super_admin.name'))), // Visível para Super Admin
                TrashedFilter::make(),
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
            'index' => Pages\ListPauseReasons::route('/'),
            'create' => Pages\CreatePauseReason::route('/create'),
            'edit' => Pages\EditPauseReason::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        $query = parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);

        if ($user && $user->hasRole(config('filament-shield.super_admin.name'))) {
            // Super admin pode ver todos (globais e de todas as empresas)
            // Se o super admin também tiver um company_id, ele ainda vê todos.
            // A filtragem por empresa específica pode ser feita pelo filtro da tabela.
            return $query;
        } elseif ($user && $user->company_id) {
            // Usuário normal vê os globais (company_id IS NULL) E os da sua própria empresa
            return $query->where(function (Builder $q) use ($user) {
                $q->whereNull('company_id')
                    ->orWhere('company_id', $user->company_id);
            });
        }
        // Se não for super admin e não tiver empresa, não vê nada (ou apenas globais, dependendo da regra)
        // Para consistência, se não tem empresa e não é super admin, não deveria ver motivos específicos de empresa.
        return $query->whereNull('company_id'); // Ou $query->whereRaw('1 = 0'); se não deve ver nada.
    }

    // Ajuste na página de criação para lidar com company_id para não super admins
    public static function getCreatePage(): string
    {
        return Pages\CreatePauseReason::class;
    }
}
