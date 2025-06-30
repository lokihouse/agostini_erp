<?php

namespace App\Filament\Resources\TransportOrderResource\RelationManagers;

use App\Models\Client;
use App\Models\TransportOrder; // Importante para type hinting do ownerRecord
use App\Services\RouteOptimizationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    // Defina um recordTitleAttribute se aplicável, ex:
    // protected static ?string $recordTitleAttribute = 'product.name';

    protected static ?string $title = "Itens para Transporte";
    protected static ?string $label = "Encomenda";
    protected static ?string $pluralLabel = "Encomendas";

    // Função auxiliar para verificar o status da ordem pai
    protected function isParentOrderCompleted(): bool
    {
        // Garante que ownerRecord é uma instância de TransportOrder antes de acessar a propriedade status
        return $this->ownerRecord instanceof TransportOrder &&
            $this->ownerRecord->status === TransportOrder::STATUS_COMPLETED;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Todos os campos do formulário de itens devem ser desabilitados
                // Adapte os campos conforme a sua definição de TransportOrderItem
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'name')
                    ->label('Cliente')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live() // Torna o campo reativo
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        if (blank($state)) {
                            $set('delivery_address_snapshot', null);
                            return;
                        }

                        $client = Client::find($state);

                        if ($client) {
                            $address = $client->getFullAddress();
                            $set('delivery_address_snapshot', $address);
                        }
                    })
                    ->disabled($this->isParentOrderCompleted()),
                Forms\Components\Textarea::make('delivery_address_snapshot')
                    ->label('Endereço de Entrega')
                    ->rows(3)
                    ->disabled()
                    ->readOnly(true)
                    ->columnSpanFull()
                    ->disabled($this->isParentOrderCompleted()),
                Forms\Components\TextInput::make('quantity')
                    ->label('Quantidade')
                    ->numeric()
                    ->required()
                    ->disabled($this->isParentOrderCompleted()),
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->label('Produto')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabled($this->isParentOrderCompleted()),
                Forms\Components\Textarea::make('notes')
                    ->label('Observações do Item')
                    ->rows(2)
                    ->columnSpanFull()
                    ->disabled($this->isParentOrderCompleted()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // ->recordTitleAttribute('product.name') // Exemplo
            ->columns([
                Tables\Columns\TextColumn::make('delivery_sequence')->label('Seq.')->sortable(),
                Tables\Columns\TextColumn::make('client.name')->label('Cliente')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('product.name')->label('Produto')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('quantity')->label('Qtd'),
                Tables\Columns\TextColumn::make('status')->label('Status do Item')
                    ->badge(),
                Tables\Columns\ImageColumn::make('delivery_photos')
                    ->label('Fotos da Entrega')
                    ->disk('public')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText(true)
                    ->toggleable(isToggledHiddenByDefault: true)
                    // Ação para abrir um modal customizado e maior
                    ->action(
                        Tables\Actions\Action::make('viewLargePhotos')
                            // Não precisamos de label ou ícone aqui, pois a própria coluna será o gatilho
                            ->modalContent(function (Model $record) {
                                // Passa as fotos para uma view Blade que renderizará o conteúdo do modal
                                return view('filament.tables.columns.delivery-photos-modal', ['photos' => $record->delivery_photos]);
                            })
                            ->modalHeading(fn(Model $record) => 'Fotos: ' . $record->product->name . ' - Cliente: ' . $record->client->name) // Título dinâmico para o modal
                            ->modalSubmitAction(false) // Remove o botão de "Submit"
                            ->modalCancelActionLabel('Fechar') // Rótulo do botão de fechar
                            ->modalWidth('4xl') // Define a largura do modal (ex: md, lg, xl, 2xl, ..., 7xl, screen)
                    ),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('Recalcular Sequencial')
                    ->visible(!$this->isParentOrderCompleted())
                    ->action(fn () => $this->recalculateSequence()),
                Tables\Actions\CreateAction::make()
                    ->visible(!$this->isParentOrderCompleted())
                    ->after(fn () => $this->recalculateSequence()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(!$this->isParentOrderCompleted())
                    ->after(fn () => $this->recalculateSequence()),
                Tables\Actions\DeleteAction::make()
                    ->visible(!$this->isParentOrderCompleted())
                    ->after(fn () => $this->recalculateSequence()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(!$this->isParentOrderCompleted())
                        ->after(fn () => $this->recalculateSequence()),
                ]),
            ]);
    }

    // Sobrescreve os métodos can* para uma camada extra de segurança
    public function canCreate(): bool
    {
        if ($this->isParentOrderCompleted()) {
            return false;
        }
        return parent::canCreate();
    }

    public function canEdit(Model $record): bool
    {
        if ($this->isParentOrderCompleted()) {
            return false;
        }
        return parent::canEdit($record);
    }

    public function canDelete(Model $record): bool
    {
        if ($this->isParentOrderCompleted()) {
            return false;
        }
        return parent::canDelete($record);
    }

    public function canDeleteAny(): bool
    {
        if ($this->isParentOrderCompleted()) {
            return false;
        }
        return parent::canDeleteAny();
    }

    private function recalculateSequence(): void
    {
        $optimizer = new RouteOptimizationService();
        $success = $optimizer->calculateSequence($this->getOwnerRecord());

        if ($success) {
            Notification::make()
                ->title('Sequência de entrega recalculada!')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Falha ao calcular a rota')
                ->body('Verifique as coordenadas da empresa e dos clientes e a chave da API do Google.')
                ->danger()
                ->send();
        }
    }
}

