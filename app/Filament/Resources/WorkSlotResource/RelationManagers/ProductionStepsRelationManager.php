<?php

namespace App\Filament\Resources\WorkSlotResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
// Importar colunas necessárias
use Filament\Tables\Columns\TextColumn;

class ProductionStepsRelationManager extends RelationManager
{
    // A relação definida no Model WorkSlot
    protected static string $relationship = 'productionSteps';

    // Título da seção na página do WorkSlot
    protected static ?string $title = 'Etapas de Produção Associadas';

    // Atributo usado para identificar a ProductionStep na seleção
    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Formulário para EDITAR a relação.
     * Como não temos campos pivot, não há o que editar aqui.
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Nenhum campo editável aqui.
            ]);
    }

    /**
     * Tabela para LISTAR as ProductionSteps relacionadas a este WorkSlot.
     */
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome da Etapa') // Traduzir
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Descrição') // Traduzir
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->description)
                    ->toggleable(isToggledHiddenByDefault: true), // Esconder por padrão
                TextColumn::make('default_order')
                    ->label('Ordem Padrão') // Traduzir
                    ->numeric()
                    ->sortable()
                    ->placeholder('-'), // Mostrar '-' se for nulo
            ])
            ->filters([
                // Filtros específicos para esta tabela, se necessário
            ])
            ->headerActions([
                // Ação para ANEXAR uma ProductionStep existente a este WorkSlot
                Tables\Actions\AttachAction::make()
                    ->label('Anexar Etapa Existente')
                    ->preloadRecordSelect() // Melhora performance da seleção
                    // Não precisamos de ->form() aqui, pois não há campos pivot
                    ->modalHeading('Anexar Etapa de Produção'),

                // REMOVER CreateAction - Criar Etapas no seu próprio Resource
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Ação para DESANEXAR a ProductionStep deste WorkSlot
                Tables\Actions\DetachAction::make(),

                // REMOVER EditAction - Não há o que editar na relação
                // Tables\Actions\EditAction::make(),

                // REMOVER DeleteAction - Perigoso deletar a Etapa principal daqui
                // Tables\Actions\DeleteAction::make(),
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
