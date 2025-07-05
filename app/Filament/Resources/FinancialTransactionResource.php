<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FinancialTransactionResource\Pages;
// use App\Filament\Resources\FinancialTransactionResource\RelationManagers; // Descomente se tiver relation managers
use App\Models\ChartOfAccount; // Para o SelectTree
use App\Models\FinancialTransaction;
use CodeWithDennis\FilamentSelectTree\SelectTree; // Mantido, pois você o usava
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn; // Para exibir valores monetários na tabela
use Illuminate\Support\Facades\Auth; // Para obter o company_id

class FinancialTransactionResource extends Resource
{
    protected static ?string $model = FinancialTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left'; // Ícone ajustado para transações
    protected static ?string $navigationGroup = 'Financeiro';
    protected static ?int $navigationSort = 53; // Ajuste conforme necessário
    protected static ?string $navigationLabel = 'Lançamentos Financeiros';
    protected static ?string $modelLabel = 'Lançamento Financeiro';
    protected static ?string $pluralModelLabel = 'Lançamentos Financeiros';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(12)->schema([
                    SelectTree::make('chart_of_account_uuid')
                        ->label('Plano de Conta')
                        ->relationship(
                            'chartOfAccount', // 1. Nome da relação (posicional)
                            'name',           // 2. Atributo para o título (posicional)
                            'parent_uuid',    // 3. Atributo para o pai (posicional)
                            fn (Builder $query) => $query->orderBy('code') // 4. Closure para modificar a query (posicional)
                        )
                        ->enableBranchNode() // Permite selecionar contas pai (se desejado, caso contrário remova)
                        ->searchable()
                        // ->withCount() // Verifique se esta opção é compatível e desejada
                        ->required()
                        ->columnSpan(4),

                    Forms\Components\Select::make('type')
                        ->label('Tipo de Lançamento')
                        ->options(FinancialTransaction::getTypeOptions()) // Usando o método do modelo
                        ->required()
                        ->searchable()
                        ->columnSpan(2),

                    Forms\Components\DatePicker::make('transaction_date')
                        ->label('Data do Lançamento')
                        ->native(false)
                        ->required()
                        ->default(now())
                        ->maxDate(now())
                        ->columnSpan(2),

                    MoneyInput::make('amount')
                        ->label('Valor')
                        ->currency('BRL') // Defina a moeda
                        ->decimals(2)
                        ->required()
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('description')
                        ->label('Descrição')
                        ->maxLength(255)
                        ->nullable()
                        ->columnSpan(6),

                    Forms\Components\TextInput::make('payment_method')
                        ->label('Método de Pagamento')
                        ->maxLength(255)
                        ->nullable()
                        ->columnSpan(3),

                    Forms\Components\TextInput::make('reference_document')
                        ->label('Documento de Referência')
                        ->helperText('Ex: NFe 123, Boleto XYZ')
                        ->maxLength(255)
                        ->nullable()
                        ->columnSpan(3),

                    Forms\Components\Textarea::make('notes')
                        ->label('Observações Adicionais')
                        ->rows(3)
                        ->nullable()
                        ->columnSpanFull(),

                    // user_id será preenchido automaticamente no backend
                    // company_id também será preenchido automaticamente
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('chartOfAccount.name')
                    ->label('Plano de Conta')
                    ->searchable()
                    ->sortable()
                    ->tooltip(fn (FinancialTransaction $record): string => $record->chartOfAccount?->code . ' - ' . $record->chartOfAccount?->name ?? '')
                    ->limit(30),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn (FinancialTransaction $record): ?string => $record->description),

                MoneyColumn::make('amount')
                    ->label('Valor')
                    ->currency('BRL')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => FinancialTransaction::getTypeOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        FinancialTransaction::TYPE_INCOME => 'success',
                        FinancialTransaction::TYPE_EXPENSE => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Registrado por')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                Tables\Filters\SelectFilter::make('chart_of_account_uuid')
                    ->label('Plano de Conta')
                    ->relationship('chartOfAccount', 'name', modifyQueryUsing: fn(Builder $query) => $query->orderBy('code'))
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo de Lançamento')
                    ->options(FinancialTransaction::getTypeOptions()),
                Tables\Filters\Filter::make('transaction_date')
                    ->form([
                        Forms\Components\DatePicker::make('transaction_date_from')->label('Lançamento de'),
                        Forms\Components\DatePicker::make('transaction_date_until')->label('Lançamento até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['transaction_date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['transaction_date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                            );
                    }),
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
            ->defaultSort('transaction_date', 'desc')
            ->emptyStateHeading('Nenhum lançamento financeiro encontrado')
            ->emptyStateDescription('Crie um lançamento para começar.');
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
            'index' => Pages\ListFinancialTransactions::route('/'),
            'create' => Pages\CreateFinancialTransaction::route('/create'),
            'edit' => Pages\EditFinancialTransaction::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // O TenantScope no modelo FinancialTransaction já cuida da filtragem por empresa.
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
