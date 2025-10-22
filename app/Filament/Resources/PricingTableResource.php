<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PricingTableResource\Pages;
use App\Models\PricingTable;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;

class PricingTableResource extends Resource
{
    protected static ?string $model = PricingTable::class;

    protected static ?string $navigationGroup = 'Financeiro';
    protected static ?string $navigationLabel = 'Precificação';
    protected static ?string $pluralLabel = 'Precificações';
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?int $navigationSort = 100; // Esse número define a ordem no menu principal 
    protected static ?int $navigationGroupSort = 5; // Opcional, se quiser forçar a ordem do grupo

    public static function getPluralLabel(): string
        {
            return 'Tabela de preços';
        }

        public static function getLabel(): string
        {
            return 'Tabela de preços';
        }

    public static function form(Form $form): Form
        {
          return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Produto')
                    ->options(Product::all()->pluck('name', 'uuid'))
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('custo_materia_prima')
                    ->label('Custo da Materia Prima')
                    ->numeric()
                    ->step(0.01)      // permite valores decimais
                    ->required(),
                Forms\Components\TextInput::make('despesas')
                    ->label('Despesas %')
                    ->numeric()
                    ->suffix('%')
                    ->step(0.01)      // permite valores decimais
                    ->required(),
                Forms\Components\TextInput::make('imposto')
                    ->label('Impostos %') 
                    ->numeric()
                    ->step(0.01)      // permite valores decimais
                    ->suffix('%')
                    ->required(),
                Forms\Components\TextInput::make('comissao')
                    ->label('Comissão %')
                    ->numeric()
                    ->step(0.01)      // permite valores decimais
                    ->suffix('%')
                    ->required(),
                Forms\Components\TextInput::make('frete')
                    ->label('Frete %')
                    ->numeric()
                    ->step(0.01)      // permite valores decimais
                    ->suffix('%')
                    ->required(),
                Forms\Components\TextInput::make('prazo')
                    ->label('Prazo 1% ao mês')
                    ->numeric()
                    ->step(0.01)      // permite valores decimais
                    ->suffix('%')
                    ->required(),
                Forms\Components\TextInput::make('vpc')
                    ->label('VPC %')
                    ->numeric()
                    ->step(0.01)      // permite valores decimais
                    ->suffix('%')
                    ->required(),
                Forms\Components\TextInput::make('assistencia')
                    ->label('Assistência %')
                    ->numeric()
                    ->step(0.01)      // permite valores decimais
                    ->suffix('%')
                    ->required(),
                Forms\Components\TextInput::make('inadimplencia')
                    ->label('Inadimplencia %')
                    ->numeric()
                    ->step(0.01)      // permite valores decimais
                    ->suffix('%')
                    ->required(),
                Forms\Components\TextInput::make('lucro')
                    ->label('Lucro %')
                    ->numeric()
                    ->step(0.01)      // permite valores decimais
                    ->suffix('%')
                    ->required(),
            ]);
        }

    public static function table(Table $table): Table
         {
             return $table
                ->columns([
                    Tables\Columns\TextColumn::make('product.name')
                        ->label('Produto')
                        ->searchable()
                        ->sortable(),
                    Tables\Columns\TextColumn::make('valorDespesas')
                        ->label('Despesas')
                        ->money('BRL')
                        ->searchable()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    Tables\Columns\TextColumn::make('custo_materia_prima')
                        ->label('Custo da Materia Prima')
                        ->money('BRL')
                        ->searchable()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    Tables\Columns\TextColumn::make('custo_produto')
                        ->label('Custo Total')
                        ->money('BRL')
                        ->searchable()
                        ->sortable(),
                    Tables\Columns\TextColumn::make('comercializacao')
                        ->label('Comercialização')
                        ->money('BRL')
                        ->searchable()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: false),
                    Tables\Columns\TextColumn::make('valorImposto')
                        ->label('Impostos')
                        ->money('BRL')
                        ->searchable()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    Tables\Columns\TextColumn::make('valorComissao')
                        ->label('Comissao')
                        ->money('BRL')
                        ->searchable()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    Tables\Columns\TextColumn::make('valorFrete')
                        ->label('Frete')
                        ->money('BRL')
                        ->searchable()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    Tables\Columns\TextColumn::make('valorPrazo')
                        ->label('Prazo 1% AM')
                        ->money('BRL')
                        ->searchable()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    Tables\Columns\TextColumn::make('valorVPC')
                        ->label('VPC')
                        ->money('BRL')
                        ->searchable()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    Tables\Columns\TextColumn::make('valorAssistencia')
                        ->label('Assistência')
                        ->money('BRL')
                        ->searchable()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    Tables\Columns\TextColumn::make('valorInadimplencia')
                        ->label('Inadimplencia')
                        ->money('BRL')
                        ->searchable()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    Tables\Columns\TextColumn::make('lucro_total')
                        ->label('Lucro Total')
                        ->searchable()
                        ->sortable()
                        ->money('BRL'),
                    Tables\Columns\TextColumn::make('preco_final')
                        ->label('Preço Final')
                        ->searchable()
                        ->sortable()
                        ->money('BRL'),
                ])
                
                ->filters([
                    TrashedFilter::make(),
                ])
                ->actions([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                ])
                ->actions([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                ])

                ->bulkActions([
                    Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make()
                    ])
                    ])
                ->headerActions([
                    Action::make('Gerar PDF')
                        ->label('Gerar PDF')
                        ->color('primary')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn () => route('pricing-table.pdf')) // usa a rota que criamos
                        ->openUrlInNewTab(), // abre em nova aba
                ]);
                
        }

   public static function getPages(): array
    {
    return [
        'index' => Pages\ListPricingTable::route('/'),
        'create' => Pages\CreatePricingTable::route('/create'),
        'edit' => Pages\EditPricingTable::route('/{record}/edit'),
    ];
    }
    
    public static function getEloquentQuery(): Builder
    { 
        return parent::getEloquentQuery() 
            ->withoutGlobalScopes([ SoftDeletingScope::class, ]);   
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['product.name'];
    }      
}