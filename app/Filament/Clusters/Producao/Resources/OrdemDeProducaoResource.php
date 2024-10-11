<?php

namespace App\Filament\Clusters\Producao\Resources;

use App\Filament\Actions\Table\OrdemDeProducaoAgendar;
use App\Filament\Actions\Table\OrdemDeProducaoCancelar;
use App\Filament\Actions\Table\OrdemDeProducaoImprimir;
use App\Filament\Actions\Table\OrdemDeProducaoProduzir;
use App\Filament\Clusters\Producao;
use App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource\Pages;
use App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource\RelationManagers;
use App\Models\OrdemDeProducao;
use App\Models\OrdemDeProducaoProduto;
use App\Models\Produto;
use App\Utils\DateHelper;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdemDeProducaoResource extends Resource
{
    protected static ?string $model = OrdemDeProducao::class;
    protected static ?string $navigationIcon = 'fas-diagram-predecessor';
    protected static ?string $cluster = Producao::class;
    protected static ?string $label = 'Ordem de Produção';
    protected static ?string $pluralLabel = 'Ordens de Produção';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(12)
            /*->disabled(function ($record) {
                return !is_null($record) && ($record->status === 'finalizada' || $record->status === 'cancelada');
            })*/
            ->schema([
                Group::make([
                    Fieldset::make('Agendamento')
                        ->columnSpan(3)
                        ->columns(2)
                        ->visible(fn ($record): bool => !$record->data_cancelamento)
                        ->schema([
                            Placeholder::make('data_inicio_agendamento')
                                ->label('Início')
                                ->content(fn ($record): string => DateHelper::fromYYYYMMDD2String($record->data_inicio_agendamento)),
                            Placeholder::make('data_final_agendamento')
                                ->label('Final')
                                ->content(fn ($record): string => DateHelper::fromYYYYMMDD2String($record->data_final_agendamento)),
                        ]),
                    Fieldset::make('Produção')
                        ->columnSpan(3)
                        ->columns(2)
                        ->visible(fn ($record): bool => !$record->data_cancelamento)
                        ->schema([
                            Placeholder::make('data_inicio_producao')
                                ->label('Início')
                                ->content(fn ($record): string => DateHelper::fromYYYYMMDD2String($record->data_inicio_producao)),
                            Placeholder::make('data_final_producao')
                                ->label('Final')
                                ->content(fn ($record): string => DateHelper::fromYYYYMMDD2String($record->data_final_producao)),
                        ]),
                    Fieldset::make('Cancelamento')
                        ->columnSpan(12)
                        ->columns(12)
                        ->visible(fn ($record): bool => !!$record->data_cancelamento)
                        ->schema([
                            Placeholder::make('data_cancelamento')
                                ->label('data')
                                ->content(fn ($record): string => DateHelper::fromYYYYMMDD2String($record->data_inicio_producao)),
                            MarkdownEditor::make('motivo_cancelamento')
                                ->columnSpan(11)
                                ->label('Motivo'),
                        ])
                ])
                    ->disabled()
                    ->columns(12)
                    ->columnSpanFull()
                    ->hidden(fn($record) => (is_null($record) || $record->status === 'rascunho')),
                Group::make([
                    Repeater::make('produtos')
                        ->label('Produtos')
                        ->defaultItems(0)
                        ->required()
                        ->itemLabel(fn ($state) => (empty($state['quantidade']) || empty($state['produto_id']) ) ? null : ($state['quantidade'] . 'x ' . Produto::query()->find($state['produto_id'])->nome))
                        ->columnSpanFull()
                        ->schema([
                            Group::make([
                                TextInput::make('quantidade')
                                    ->label('Quantidade')
                                    ->numeric()
                                    ->columnSpan(1),
                                Select::make('produto_id')
                                    ->label('Produto')
                                    ->options(Produto::query()->pluck('nome', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->columnSpan(7),
                            ])
                            ->columns(8)
                        ]),
                ])
                    ->hidden(fn($record) => !(is_null($record) || $record->status === 'rascunho' || $record->status === 'agendada'))
                    ->columnSpanFull(),
                Group::make([
                    ViewField::make('produtos')
                        ->view('forms.components.ordem_de_producao_produtos_tabela')
                        ->columnSpan(6)
                ])
                    ->columns(12)
                    ->hidden(fn($record) => (is_null($record) || $record->status === 'rascunho' || $record->status === 'agendada'))
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->filters([
                SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        'rascunho' => 'Rascunho',
                        'agendada' => 'Agendada',
                        'em_producao' => 'Em Produção',
                        'finalizada' => 'Finalizada',
                        'cancelada' => 'Cancelada',
                    ])
            ])
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->extraHeaderAttributes(['style'=>'width: 1px']),
                TextColumn::make('status')
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        default => 'Rascunho',
                        'agendada' => 'Agendada',
                        'em_producao' => 'Em Produção',
                        'cancelada' => 'Cancelada',
                        'finalizada' => 'Finalizada',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        default => 'gray',
                        'agendada' => 'warning',
                        'em_producao' => 'info',
                        'cancelada' => 'danger',
                        'finalizada' => 'success',
                    })
                    ->badge(),
                ColumnGroup::make('Agendamento', [
                    TextColumn::make('data_inicio_agendamento')
                        ->label('Início')
                        ->alignCenter()
                        ->date('d/m/Y')
                        ->extraHeaderAttributes(['style'=>'width: 125px']),
                    TextColumn::make('data_final_agendamento')
                        ->label('Final')
                        ->alignCenter()
                        ->date('d/m/Y')
                        ->extraHeaderAttributes(['style'=>'width: 125px']),
                ])->alignCenter(),
                ColumnGroup::make('Produção', [
                    TextColumn::make('data_inicio_producao')
                        ->label('Início')
                        ->alignCenter()
                        ->date('d/m/Y')
                        ->extraHeaderAttributes(['style'=>'width: 125px']),
                    TextColumn::make('data_final_producao')
                        ->label('Final')
                        ->alignCenter()
                        ->date('d/m/Y')
                        ->extraHeaderAttributes(['style'=>'width: 125px']),
                ])->alignCenter(),
                TextColumn::make('data_cancelamento')
                    ->label('Cancel.')
                    ->alignCenter()
                    ->date('d/m/Y')
                    ->extraHeaderAttributes(['style'=>'width: 125px']),
            ])
            ->actionsPosition(Tables\Enums\ActionsPosition::BeforeColumns)
            ->actions([
                OrdemDeProducaoImprimir::make('imprimir'),
                OrdemDeProducaoProduzir::make('produzir'),
                OrdemDeProducaoAgendar::make('agendar'),
                OrdemDeProducaoCancelar::make('cancelar')
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
            'create' => Pages\CreateOrdemDeProducao::route('/create'),
            'edit' => Pages\EditOrdemDeProducao::route('/{record}/edit'),
            'print' => Pages\PrintOrdemDeProducao::route('/{record}/print'),
        ];
    }
}
