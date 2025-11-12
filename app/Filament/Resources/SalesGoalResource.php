<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesGoalResource\Pages;
use App\Models\SalesGoal;
use App\Models\User; // Para buscar vendedores
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get; // Import Get
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rules\Unique;

class SalesGoalResource extends Resource
{
    protected static ?string $model = SalesGoal::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $modelLabel = 'Meta de Venda';
    protected static ?string $pluralModelLabel = 'Metas de Vendas';
    protected static ?string $navigationGroup = 'Vendas';
    protected static ?int $navigationSort = 45; // Ajuste conforme sua preferência

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Vendedor')
                    ->relationship(
                        name: 'user',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query) {
                            return $query
                                ->where('is_active', true)
                                ->where('company_id', auth()->user()->company_id)
                                ->whereHas('roles', fn(Builder $q) => $q->whereIn('name', ['Vendedor', 'Administrador']));
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(), // Adicionado ->live() para que a validação do período reaja à mudança do vendedor
                Forms\Components\DatePicker::make('period')
                    ->label('Mês/Ano da Meta')
                    ->native(false)
                    ->displayFormat('m/Y') // Mostra apenas mês e ano
                    ->required()
                    ->helperText('Selecione qualquer dia do mês desejado. O sistema salvará o primeiro dia do mês.')
                    ->unique(
                        table: SalesGoal::class,
                        column: 'period',
                        ignoreRecord: true,
                        // CORREÇÃO AQUI: Altere o type hint de $rule
                        modifyRuleUsing: function (Unique $rule, callable $get) { // Alterado de Rule para Unique
                            $userId = $get('user_id');
                            $periodState = $get('period'); // Obtém o estado atual do campo 'period'

                            if ($userId && $periodState) {
                                $periodDate = Carbon::parse($periodState)->startOfMonth()->toDateString();
                                $rule->where('user_id', $userId)
                                    ->where('period', $periodDate);
                            }
                            return $rule;
                        }
                    )
                    ->validationMessages([
                        'unique' => 'Já existe uma meta para este vendedor no período selecionado.',
                    ]),
                Forms\Components\TextInput::make('goal_amount')
                    ->label('Valor da Meta (R$)')
                    ->numeric()
                    ->prefix('R$')
	                    ->required()
	                    ->minValue(0),
	
	                Forms\Components\Select::make('commission_type')
	                    ->label('Tipo de Comissão')
	                    ->options([
	                        'goal' => 'Comissão por Meta Alcançada',
	                        'sale' => 'Comissão por Venda',
	                    ])
	                    ->default('goal')
	                    ->required()
	                    ->live()
	                    ->afterStateUpdated(fn (Forms\Set $set) => $set('commission_percentage', 0)), // Zera a porcentagem ao mudar o tipo
	
	                Forms\Components\TextInput::make('commission_percentage')
	                    ->label('Porcentagem da Comissão (%)')
	                    ->numeric()
	                    ->suffix('%')
	                    ->required()
	                    ->minValue(0)
	                    ->maxValue(100)
	                    ->visible(fn (Forms\Get $get): bool => in_array($get('commission_type'), ['goal', 'sale'])),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Vendedor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('period')
                    ->label('Período')
                    ->date('M/Y') // Formato Mês/Ano
                    ->sortable(),
                Tables\Columns\TextColumn::make('goal_amount')
                    ->label('Valor da Meta')
	                    ->money('BRL')
	                    ->sortable(),
	            Tables\Columns\TextColumn::make('commission_type')
	                ->label('Tipo Comissão')
	                ->formatStateUsing(fn (string $state): string => match ($state) {
	                    'goal' => 'Por Meta',
	                    'sale' => 'Por Venda',
	                    default => $state,
	                })
	                ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
	            Tables\Columns\TextColumn::make('commission_percentage')
	                ->label('% Comissão')
	                ->suffix('%')
	                ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
             ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Vendedor')
                    ->relationship('user', 'name', modifyQueryUsing: fn (Builder $query) =>
                        $query->where('company_id', auth()->user()->company_id) // 🔑 filtro pela empresa
                         ->whereHas('roles', fn(Builder $q) => 
                         $q->whereIn('name', ['Vendedor', 'Administrador'])))
                    ->searchable()
                    ->preload(),
                // TODO: Adicionar filtro por período (mês/ano) se necessário
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
            ->defaultSort('period', 'desc');
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
            'index' => Pages\ListSalesGoals::route('/'),
            'create' => Pages\CreateSalesGoal::route('/create'),
            'edit' => Pages\EditSalesGoal::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
