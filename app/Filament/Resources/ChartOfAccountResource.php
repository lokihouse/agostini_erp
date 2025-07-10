<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChartOfAccountResource\Pages;
// use App\Filament\Resources\ChartOfAccountResource\RelationManagers; // Descomente se tiver relation managers
use App\Models\ChartOfAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth; // Para obter o company_id do usuário logado

class ChartOfAccountResource extends Resource
{
    protected static ?string $model = ChartOfAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet'; // Ícone ajustado
    protected static ?string $navigationGroup = 'Financeiro';
    protected static ?int $navigationSort = 51; // Ajuste conforme necessário
    protected static ?string $navigationLabel = 'Plano de Contas'; // Mantido
    protected static ?string $modelLabel = 'Conta Contábil'; // Ajustado para singular
    protected static ?string $pluralModelLabel = 'Plano de Contas'; // Mantido

    public static function form(Form $form): Form
    {
        return $form
            ->columns(12)
            ->schema([
                Forms\Components\Select::make('parent_uuid')
                    ->label('Conta Pai')
                    ->relationship(
                        name: 'parentAccount', // Nome da relação no modelo ChartOfAccount
                        titleAttribute: 'name', // Atributo para exibir no select
                        modifyQueryUsing: fn (Builder $query) => $query->orderBy('code') // Ordena pela coluna 'code'
                    )
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Selecione a conta de nível superior, se aplicável.')
                    ->columnSpan(3), // Ajustado para melhor layout

                Forms\Components\Select::make('type')
                    ->label('Tipo da Conta')
                    ->options(ChartOfAccount::getTypeOptions()) // Usando o método do modelo
                    ->required()
                    ->columnSpan(3), // Ajustado

                Forms\Components\TextInput::make('name')
                    ->label('Nome da Conta')
                    ->required()
                    ->maxLength(100)
                    ->columnSpan(3), // Ajustado

                // O campo 'code' será gerado automaticamente na página de criação
                Forms\Components\TextInput::make('code')
                    ->hidden(true)
                    ->label('Código da Conta')
                    ->disabled() // Desabilitado no formulário, pois é gerado
                    ->dehydrated(false) // Não envia este valor do form, pois será definido no backend
                    ->visibleOn('edit') // Visível apenas na edição
                    ->columnSpan(12),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => ChartOfAccount::getTypeOptions()[$state] ?? $state)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('parentAccount.name') // Usando a relação correta
                ->label('Conta Pai')
                    ->placeholder('N/A')
                    ->searchable()
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo da Conta')
                    ->options(ChartOfAccount::getTypeOptions()),
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
            ->defaultSort('code', 'asc') // Ordenação padrão por código
            ->emptyStateHeading('Nenhuma conta encontrada')
            ->emptyStateDescription('Crie uma conta para começar.');
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\FinancialTransactionsRelationManager::class, // Exemplo se você criar um
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChartOfAccounts::route('/'),
            'create' => Pages\CreateChartOfAccount::route('/create'),
            'edit' => Pages\EditChartOfAccount::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // O TenantScope no modelo ChartOfAccount já cuida da filtragem por empresa.
        // Se precisar de lógica adicional específica para este resource, adicione aqui.
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

