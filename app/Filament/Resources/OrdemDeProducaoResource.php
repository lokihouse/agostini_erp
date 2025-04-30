<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdemDeProducaoResource\Pages;
use App\Filament\Resources\OrdemDeProducaoResource\RelationManagers;
use App\Forms\Components\OrdemDeProducaoEtapasField;
use App\Forms\Components\OrdemDeProducaoEventosField;
use App\Forms\Components\Produto_EtapasDeProducao_Form;
use App\Forms\Components\ProdutosPorOrdemDeProducaoField;
use App\Models\OrdemDeProducao;
use App\Models\Produto;
use App\Models\ProdutoPorOrdemDeProducao;
use Filament\Actions\StaticAction;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use IbrahimBougaoua\FilaProgress\Tables\Columns\ProgressBar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class OrdemDeProducaoResource extends Resource
{
    protected static ?string $model = OrdemDeProducao::class;
    protected static ?string $navigationGroup = 'Produção';
    protected static ?string $label = 'Ordem de Produção';
    protected static ?string $pluralLabel = 'Ordens de Produção';
    protected static ?int $navigationSort = 20;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(13)
            ->schema([
                Group::make([
                    Section::make('Ordem de Produção')
                        ->compact()
                        ->columnSpan(3)
                        ->schema([
                            Placeholder::make('cliente.nome_fantasia')
                                ->visible(fn ($record) => $record->cliente)
                                ->label('Cliente')
                                ->content(fn ($record) => new HtmlString('<span class="badge badge-primary">' . $record->cliente->nome_fantasia . '</span>')),
                            Placeholder::make('cliente.nome_fantasia')
                                ->visible(fn ($record) => !$record->cliente)
                                ->label('')
                                ->content(fn ($record) => new HtmlString('<span class="badge badge-primary"> ORDEM DE PRODUÇÃO INTERNA </span>')),
                            Placeholder::make('status')
                                ->label('Status')
                                ->content(fn ($record) => new HtmlString('<span class="badge badge-primary">' . $record->status . '</span>')),
                        ]),
                    Section::make('Produtos')
                        ->columnSpan(4)
                        ->compact()
                        ->schema([
                            ProdutosPorOrdemDeProducaoField::make('produtos')
                                ->label('')
                        ]),
                    Section::make('Eventos')
                        ->compact()
                        ->columnSpanFull()
                        ->schema([
                            OrdemDeProducaoEventosField::make('eventos')->label('')
                        ])
                ])
                    ->columns(7)
                    ->columnSpan(7),
                Section::make('Etapas')
                    ->columnSpan(6)
                    ->compact()
                    ->schema([
                        OrdemDeProducaoEtapasField::make('etapas')->label('')
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->width(1),
                TextColumn::make('status')
                    ->label('Status')
                    ->width(1)
                    ->badge(),
                TextColumn::make('cliente.nome_fantasia'),
                ProgressBar::make('andamento')
                    ->width('150px')
                    ->alignCenter()
                    ->getStateUsing(function ($record) {
                        return [
                            'total' => 100,
                            'progress' => 0,
                        ];
                    }),
                TextColumn::make('produtos_count')
                    ->counts('produtos')
                    ->alignCenter()
                    ->label('Produtos')
                    ->width(1),
                TextColumn::make('data_programacao')
                    ->alignCenter()
                    ->label('Criação')
                    ->width(1)
                    ->date('d/m/y')
                    ->badge(),
                TextColumn::make('data_producao')
                    ->alignCenter()
                    ->label('Produção')
                    ->width(1)
                    ->date('d/m/y')
                    ->badge(),
                TextColumn::make('data_finalizacao')
                    ->alignCenter()
                    ->label('Finalização')
                    ->width(1)
                    ->date('d/m/y')
                    ->badge(),
                TextColumn::make('data_cancelamento')
                    ->alignCenter()
                    ->label('Cancelamento')
                    ->width(1)
                    ->date('d/m/y')
                    ->badge(),
                TextColumn::make('justificativa')
                    ->label('Justificativa')
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListOrdemDeProducaos::route('/'),
            'edit' => Pages\EditOrdemDeProducao::route('/{record}/edit'),
        ];
    }
}
