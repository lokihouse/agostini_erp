<?php

namespace App\Filament\Resources\ProductionOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

// Importar componentes e modelos necessários
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn; // Se precisar de ícones
use Filament\Tables\Filters\TrashedFilter; // Para Soft Deletes
use App\Models\ProductionStep; // Para Select de Etapas
use App\Models\WorkSlot; // Para Select de Locais
use App\Models\User; // Para Select de Usuários
use Illuminate\Support\Facades\Auth; // Para pegar usuário logado
// Importar Pages para checar contexto (Create/Edit)
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;


class ProductionLogsRelationManager extends RelationManager
{
    // Relação definida no Model ProductionOrderItem
    protected static string $relationship = 'productionLogs';

    // Título da seção na página de Edição do Item da Ordem
    protected static ?string $title = 'Registros de Produção (Logs)';

    // Um log não tem um "título" único óbvio, então removemos isso
    // protected static ?string $recordTitleAttribute = 'user.name';

    /**
     * Formulário para CRIAR ou EDITAR um ProductionLog.
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('production_step_uuid')
                    ->label('Etapa Realizada')
                    ->relationship('productionStep', 'name') // Busca na relação 'productionStep' do ProductionLog
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpan(1), // Ocupa 1 coluna

                Select::make('work_slot_uuid')
                    ->label('Local de Trabalho (Opcional)')
                    ->relationship('workSlot', 'name') // Busca na relação 'workSlot' do ProductionLog
                    ->searchable()
                    ->preload()
                    ->nullable() // Permite valor nulo
                    ->columnSpan(1), // Ocupa 1 coluna

                TextInput::make('quantity')
                    ->label('Quantidade Produzida')
                    ->numeric()
                    ->required()
                    ->minValue(0) // Ou 0.0001 se não puder ser zero
                    ->step('0.0001') // Define o passo para decimais (ajuste conforme necessário)
                    ->columnSpan(1),

                DateTimePicker::make('log_time')
                    ->label('Data/Hora do Registro')
                    ->required()
                    ->seconds(false) // Não mostrar segundos
                    ->default(now()) // Padrão para data/hora atual
                    ->columnSpan(1),

                Select::make('user_uuid')
                    ->label('Usuário Responsável')
                    ->relationship('user', 'name') // Busca na relação 'user' do ProductionLog
                    ->searchable()
                    ->preload()
                    ->required()
                    // Define o usuário logado como padrão ao CRIAR
                    // Usamos $operation em vez de $livewire para v3+
                    ->default(fn (string $operation): ?string => $operation === 'create' ? Auth::id() : null)
                    // Torna read-only na edição para não mudar quem registrou? (Opcional)
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->columnSpan(2), // Ocupa 2 colunas

                Textarea::make('notes')
                    ->label('Observações')
                    ->columnSpanFull(), // Ocupa largura total
            ])->columns(2); // Define 2 colunas para o layout do formulário
    }

    /**
     * Tabela para LISTAR os ProductionLogs deste ProductionOrderItem.
     */
    public function table(Table $table): Table
    {
        return $table
            // ->recordTitleAttribute('user.name') // Removido
            ->columns([
                TextColumn::make('log_time')
                    ->label('Data/Hora')
                    ->dateTime('d/m/Y H:i') // Formato BR
                    ->sortable()
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Usuário')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('productionStep.name')
                    ->label('Etapa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('workSlot.name')
                    ->label('Local')
                    ->placeholder('-') // Mostrar '-' se for nulo
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Esconder por padrão

                TextColumn::make('quantity')
                    ->label('Qtd. Produzida')
                    ->numeric(decimalPlaces: 4) // Exibir com a precisão definida
                    ->alignEnd() // Alinhar à direita para números
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Observações')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->notes)
                    ->toggleable(isToggledHiddenByDefault: true), // Esconder por padrão

                TextColumn::make('created_at')
                    ->label('Registrado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at') // Para SoftDeletes
                ->label('Excluído em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(), // Adicionar filtro para SoftDeletes
                // TODO: Adicionar filtros por Etapa, Usuário, Data?
                // Exemplo:
                // Tables\Filters\SelectFilter::make('production_step_uuid')
                //     ->relationship('productionStep', 'name')
                //     ->label('Etapa'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Adicionar Registro')
                    ->modalHeading('Adicionar Registro de Produção'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Editar Registro de Produção'),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(), // Para SoftDeletes
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(), // Para SoftDeletes
                    Tables\Actions\RestoreBulkAction::make(), // Para SoftDeletes
                ]),
            ])
            ->defaultSort('log_time', 'desc'); // Ordenar pelos registros mais recentes primeiro
        // ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([SoftDeletingScope::class])) // Se precisar mostrar logs excluídos por padrão
    }

    // Necessário para o filtro TrashedFilter e ações de restore funcionarem corretamente
    // Descomente se o filtro/restore não funcionar como esperado
    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()
    //         ->withoutGlobalScopes([
    //             SoftDeletingScope::class,
    //         ]);
    // }
}
