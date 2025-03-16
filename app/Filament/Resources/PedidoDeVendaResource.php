<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PedidoResource\Pages;
use App\Filament\Resources\PedidoResource\RelationManagers;
use App\Models\Pedido;
use App\Models\PedidoDeVenda;
use App\Models\Produto;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Illuminate\Support\Number;

class PedidoDeVendaResource extends ResourceBase
{
    protected static ?string $model = PedidoDeVenda::class;
    protected static ?string $navigationGroup = 'Vendas';
    protected static ?int $navigationSort = 31;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(6)
            ->disabled(true)
            ->schema([
                Group::make([
                    Select::make('cliente_id')
                        ->relationship('cliente', 'nome_fantasia'),
                    Select::make('user_id')
                        ->relationship('vendedor', 'nome')
                ]),
                Group::make([
                    Placeholder::make('')
                        ->visible(fn($record) => $record->status === 'cancelado')
                        ->content(new HtmlString('<div class="bg-red-500 rounded-xl p-4 text-6xl text-center text-white">PEDIDO CANCELADO</div>')),
                    Placeholder::make('')
                        ->visible(fn($record) => $record->status === 'processado')
                        ->content(new HtmlString('<div class="bg-primary-500 rounded-xl p-4 text-6xl text-center text-white">PEDIDO EM PRODUÇÃO</div>')),
                    Repeater::make('produtos')
                        ->relationship('produtos')
                        ->grid(3)
                        ->addable(false)
                        ->deletable(false)
                        ->disabled(fn($record) => $record && $record->status !== 'novo')
                        ->columns(8)
                        ->reorderable(false)
                        ->cloneable(false)
                        ->collapsible(false)
                        ->schema([
                            Placeholder::make('quantidade')
                                ->columnSpan(2)
                                ->label('Qnt.')
                                ->content(fn ($state) => $state),
                            Placeholder::make('produto_id')
                                ->columnSpan(6)
                                ->label('Nome')
                                ->content(fn ($state, $record) => Produto::query()->where('id', $state)->withTrashed()->first()->nome),
                            Placeholder::make('valor_original')
                                ->columnSpan(3)
                                ->label('R$ Un.')
                                ->content(fn ($state, $record) => "R$ " . number_format($state, 2, ',','.')),
                            Placeholder::make('desconto')
                                ->columnSpan(2)
                                ->label('Desc.')
                                ->content(fn ($state, $record) => number_format($state, 2, ',','.') . " %"),
                            Placeholder::make('subtotal')
                                ->columnSpan(3)
                                ->label('R$ Subtotal')
                                ->content(fn ($state, $record) => "R$ " . number_format($state, 2, ',','.')),
                        ]),
                ])->columnSpan(5)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->width(50),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'novo' => 'gray',
                        'processado' => 'primary',
                        'cancelado' => 'danger',
                    })
                    ->width(100),
                TextColumn::make('cliente.nome_fantasia')
                    ->width(300),
                TextColumn::make('vendedor.nome'),
                TextColumn::make('vendedor.meta_mensal_de_venda')
                    ->label('Meta de Vendas')
                    ->formatStateUsing(fn($state) => Number::format(floatval($state) / 100, 2))
                    ->hidden(),
                TextColumn::make('produtos')
                    ->label('Num de Produtos')
                    ->formatStateUsing(function ($state) {
                        $produtos = json_decode("[" . $state . "]");
                        return array_reduce($produtos, fn ($carry, $item) => $carry + $item->quantidade, 0);
                    })
                    ->width(1),
                TextColumn::make('valor_de_produtos')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => "R$ " . number_format(floatval($state), 2, ',','.'))
                    ->alignCenter()
                    ->width(1),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->multiple()
                    ->options(['novo' => 'Novo', 'processado' => 'Processado', 'cancelado' => 'Cancelado']),
                SelectFilter::make('cliente_id')
                    ->label('Cliente')
                    ->relationship('cliente', 'nome_fantasia'),
                SelectFilter::make('user_id')
                    ->label('Vendedor')
                    ->relationship('vendedor', 'nome'),
            ])
            ->bulkActions([
                ExportBulkAction::make()
            ]);
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
            'index' => Pages\ListPedidosDeVenda::route('/'),
            'edit' => Pages\EditPedidoDeVenda::route('/{record}/edit'),
        ];
    }
}
