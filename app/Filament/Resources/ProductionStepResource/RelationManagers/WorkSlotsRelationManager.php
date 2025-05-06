<?php

namespace App\Filament\Resources\ProductionStepResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
// Importar componentes/colunas necessários
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn; // Para o status ativo/inativo

class WorkSlotsRelationManager extends RelationManager
{
    protected static string $relationship = 'workSlots';

    // Definir título amigável
    protected static ?string $title = 'Locais de Trabalho Associados';

    // Atributo usado para identificar o registro (WorkSlot) na seleção
    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Formulário para EDITAR a relação.
     * Como não temos campos pivot extras (como 'step_order' no outro manager),
     * este formulário não é necessário para a EditAction (que será removida).
     * Deixamos vazio ou apenas com campos não editáveis se usássemos ViewAction.
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Nenhum campo editável aqui, pois só estamos gerenciando a *relação*.
                // Os detalhes do WorkSlot são editados no seu próprio Resource.
            ]);
    }

    /**
     * Tabela para LISTAR os WorkSlots relacionados a esta ProductionStep.
     */
    public function table(Table $table): Table
    {
        return $table
            // ->recordTitleAttribute('name') // Definido na propriedade estática $recordTitleAttribute
            ->columns([
                TextColumn::make('name')
                    ->label('Nome do Local') // Traduzir
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location')
                    ->label('Localização') // Traduzir
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'), // Mostrar '-' se for nulo
                IconColumn::make('is_active') // Usar IconColumn para booleanos
                ->label('Ativo') // Traduzir
                ->boolean() // Define os ícones padrão para true/false
                ->sortable(),
                TextColumn::make('description')
                    ->label('Descrição') // Traduzir
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->description)
                    ->toggleable(isToggledHiddenByDefault: true), // Esconder por padrão
            ])
            ->filters([
                // Filtros específicos para esta tabela, se necessário
            ])
            ->headerActions([
                // Ação para ANEXAR um WorkSlot existente a esta ProductionStep
                Tables\Actions\AttachAction::make()
                    ->label('Anexar Local Existente')
                    ->preloadRecordSelect() // Melhora performance da seleção
                    // Não precisamos de ->form() aqui, pois não há campos pivot
                    ->modalHeading('Anexar Local de Trabalho'),

                // REMOVER CreateAction - É melhor criar WorkSlots no seu próprio Resource
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Ação para DESANEXAR o WorkSlot desta ProductionStep
                Tables\Actions\DetachAction::make(),

                // REMOVER EditAction - Não há o que editar na relação em si
                // Tables\Actions\EditAction::make(),

                // REMOVER DeleteAction - Perigoso deletar o WorkSlot principal daqui
                // Tables\Actions\DeleteAction::make(),

                // Poderíamos adicionar ViewAction se quiséssemos ver detalhes do WorkSlot
                // Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Ação para DESANEXAR em massa
                    Tables\Actions\DetachBulkAction::make(),

                    // REMOVER DeleteBulkAction - Perigoso
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            // Ordenação padrão da tabela
            ->defaultSort('name', 'asc');
    }
}
