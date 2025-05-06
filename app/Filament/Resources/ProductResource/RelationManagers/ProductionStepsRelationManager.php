<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

// Importar componentes necessários
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Validation\Rule;

class ProductionStepsRelationManager extends RelationManager
{
    protected static string $relationship = 'productionSteps';

    // Definir o título que aparecerá na interface
    protected static ?string $title = 'Etapas de Produção';

    // Definir o label do modelo relacionado (opcional, mas bom para tradução)
    protected static ?string $recordTitleAttribute = 'name'; // Já definimos isso no comando

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Campo para selecionar a Etapa (geralmente não editamos a etapa aqui, só selecionamos)
                // O Filament geralmente lida com a seleção no AttachAction.
                // Mas precisamos adicionar o campo PIVOT aqui para quando editamos a relação.
                TextInput::make('step_order')
                    ->label('Ordem da Etapa')
                    ->numeric()
                    ->required()
                    ->rule(function (RelationManager $livewire, $record) {
                        return Rule::unique('product_production_step', 'step_order')
                            ->where('product_uuid', $livewire->getOwnerRecord()->uuid)
                            ->ignore($record ? $record->pivot->id : null, 'id');
                    })
                    ->helperText('Define a sequência em que esta etapa ocorre para este produto.'),

                // Campo 'name' da ProductionStep (geralmente não é editável aqui)
                // Forms\Components\TextInput::make('name')
                //     ->required()
                //     ->maxLength(255)
                //     ->disabled(), // Desabilitado pois estamos apenas relacionando
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // Habilitar reordenação pela coluna 'step_order' da pivot
            ->reorderable('step_order')
            ->columns([
                TextColumn::make('step_order') // Adicionar a coluna da ordem da pivot
                ->label('Ordem')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nome da Etapa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(50) // Limitar tamanho na tabela
                    ->tooltip(fn($record) => $record->description) // Mostrar completo no tooltip
                    ->toggleable(isToggledHiddenByDefault: true), // Esconder por padrão

                // Adicionar outras colunas da ProductionStep se necessário
            ])
            ->filters([
                // Filters for the relation table (if needed)
            ])
            ->headerActions([
                // Ação para ANEXAR uma Etapa existente ao Produto
                Tables\Actions\AttachAction::make()
                    ->label('Anexar Etapa Existente')
                    // Adicionar o campo pivot 'step_order' ao modal de anexar
                    ->form(fn(Tables\Actions\AttachAction $action): array => [
                        // Campo para selecionar a Etapa (o AttachAction já faz isso)
                        $action->getRecordSelect(),
                        // Campo para definir a ordem NO MOMENTO de anexar
                        TextInput::make('step_order')
                            ->label('Ordem da Etapa')
                            ->numeric()
                            ->required()
                            ->rule(function (RelationManager $livewire) {
                                return Rule::unique('product_production_step', 'step_order')
                                    ->where('product_uuid', $livewire->getOwnerRecord()->uuid);
                            })
                            ->default(fn(RelationManager $livewire) => ($livewire->getOwnerRecord()->productionSteps()->max('step_order') ?? 0) + 1) // Sugere a próxima ordem
                            ->helperText('Define a sequência para este produto.'),
                    ])
                    ->preloadRecordSelect() // Pré-carrega as opções para performance
                    ->modalHeading('Anexar Etapa de Produção'),

                // Ação para CRIAR uma nova Etapa e já anexar (opcional)
                // Tables\Actions\CreateAction::make()
                //     ->label('Criar e Anexar Nova Etapa')
                //     ->form([ // Definir campos para criar a NOVA etapa
                //         TextInput::make('name')->required()->maxLength(255),
                //         Forms\Components\Textarea::make('description'),
                //         TextInput::make('default_order')->numeric(),
                //         // Adicionar o campo pivot 'step_order' também aqui
                //         TextInput::make('step_order')->label('Ordem da Etapa (para este produto)')->numeric()->required()->default(1),
                //     ]),
            ])
            ->actions([
                // Ação para EDITAR os dados da PIVOT (a ordem da etapa)
                Tables\Actions\EditAction::make()
                    ->label('Editar Ordem'), // Mudar o label para refletir o que se edita

                // Ação para DESANEXAR a Etapa do Produto
                Tables\Actions\DetachAction::make(),

                // Ação para DELETAR a Etapa (cuidado, deleta o registro principal de Etapa)
                // Tables\Actions\DeleteAction::make(), // Geralmente não queremos deletar a etapa daqui

                // Ação para VISUALIZAR a Etapa (opcional)
                // Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    // Tables\Actions\DeleteBulkAction::make(), // Cuidado ao habilitar
                ]),
            ])
            // Ordenação padrão pela coluna da pivot
            ->defaultSort('step_order', 'asc');
    }
}
