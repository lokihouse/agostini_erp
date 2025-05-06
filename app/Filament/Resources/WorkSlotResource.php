<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkSlotResource\Pages;

// Importar Relation Manager
use App\Filament\Resources\WorkSlotResource\RelationManagers;
use App\Filament\Resources\WorkSlotResource\RelationManagers\ProductionStepsRelationManager;

// Adicionar esta linha
use App\Models\WorkSlot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

// Para unique rule
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;

// Para agrupar
use Filament\Tables\Filters\TrashedFilter;

// Para filtro de excluídos
use Filament\Forms\Components\Toggle;

// Para o campo is_active
use Filament\Tables\Columns\IconColumn;

// Para a coluna is_active
use Filament\Tables\Columns\TextColumn;
use Illuminate\Validation\Rule;

// Para colunas de texto

class WorkSlotResource extends Resource
{
    protected static ?string $model = WorkSlot::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront'; // Ícone mais relacionado a local/posto
    protected static ?string $modelLabel = 'Local de Trabalho'; // Nome singular
    protected static ?string $pluralModelLabel = 'Locais de Trabalho'; // Nome plural

    protected static ?string $navigationGroup = 'Produção';
    protected static ?int $navigationSort = 3; // Ordem na navegação (depois de Etapas)

    // Configuração para busca global
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detalhes do Local de Trabalho')
                    ->columns(2) // Dividir em colunas
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome do Local') // Traduzir
                            ->required()
                            ->maxLength(255)
                            // Garantir nome único, ignorando o registro atual na edição
                            ->rule(function ($record) { // <-- Passe $record como argumento para a closure
                                return Rule::unique('work_slots', 'name') // Tabela e coluna corretas
                                ->where('company_id', auth()->user()->company_id) // Condição da empresa
                                ->ignore($record?->uuid); // Usa o $record injetado para ignorar
                            })
                            ->columnSpan(1), // Ocupa 1 coluna

                        Forms\Components\TextInput::make('location')
                            ->label('Localização (Opcional)') // Traduzir
                            ->maxLength(255)
                            ->default(null)
                            ->columnSpan(1), // Ocupa 1 coluna

                        Forms\Components\Textarea::make('description')
                            ->label('Descrição') // Traduzir
                            ->columnSpanFull(), // Ocupa largura total

                        Toggle::make('is_active')
                            ->label('Ativo') // Traduzir
                            ->required()
                            ->default(true) // Definir ativo como padrão ao criar
                            ->columnSpanFull(), // Ocupa largura total
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('uuid') // Esconder UUID
                //     ->label('UUID')
                //     ->searchable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->label('Nome do Local') // Traduzir
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location')
                    ->label('Localização') // Traduzir
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'), // Mostrar '-' se for nulo
                IconColumn::make('is_active') // Usar IconColumn para boolean
                ->label('Ativo') // Traduzir
                ->boolean() // Define ícones padrão true/false
                ->sortable(),
                TextColumn::make('created_at')
                    ->label('Criado em') // Traduzir
                    ->dateTime('d/m/Y H:i') // Formato BR
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Atualizado em') // Traduzir
                    ->dateTime('d/m/Y H:i') // Formato BR
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('Excluído em') // Traduzir
                    ->dateTime('d/m/Y H:i') // Formato BR
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(), // Adicionar filtro de excluídos
            ])
            ->actions([
                // ViewAction removida conforme solicitado
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), // Adicionar ação de deletar
                Tables\Actions\RestoreAction::make(), // Adicionar ação de restaurar
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(), // Adicionar exclusão permanente
                    Tables\Actions\RestoreBulkAction::make(), // Adicionar restauração em massa
                ]),
            ])
            // Ordenação padrão
            ->defaultSort('name', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            // Registrar o Relation Manager para as Etapas de Produção
            RelationManagers\ProductionStepsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkSlots::route('/'),
            'create' => Pages\CreateWorkSlot::route('/create'),
            // View page removida conforme solicitado
            'edit' => Pages\EditWorkSlot::route('/{record}/edit'),
        ];
    }

    // Necessário para o filtro TrashedFilter funcionar corretamente
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    // Opcional: Configuração para busca global mais específica
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'location', 'description']; // Campos usados na busca global
    }
}
