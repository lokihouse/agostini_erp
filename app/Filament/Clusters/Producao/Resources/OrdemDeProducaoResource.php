<?php

namespace App\Filament\Clusters\Producao\Resources;

use App\Filament\Actions\Table\OrdemDeProducaoAgendar;
use App\Filament\Actions\Table\OrdemDeProducaoCancelar;
use App\Filament\Actions\Table\OrdemDeProducaoImprimir;
use App\Filament\Clusters\Producao;
use App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource\Pages;
use App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource\RelationManagers;
use App\Http\Controllers\ProdutoController;
use App\Models\OrdemDeProducao;
use App\Models\OrdemDeProducaoProduto;
use App\Models\Produto;
use App\Utils\DateHelper;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

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
            ->disabled(function ($record) {
                return !is_null($record) && ($record->status === 'finalizada' || $record->status === 'cancelada');
            })
            ->schema([
                Group::make([
                    Fieldset::make('Agendamento')
                        ->columns(2)
                        ->schema([
                            DatePicker::make('data_inicio_agendamento')
                                ->label('Início'),
                            DatePicker::make('data_final_agendamento')
                                ->label('Final'),
                        ]),
                    Fieldset::make('Produção')
                        ->columns(2)
                        ->schema([
                            DatePicker::make('data_inicio_producao')
                                ->label('Início'),
                            DatePicker::make('data_final_producao')
                                ->label('Final'),
                        ]),
                    Fieldset::make('Cancelamento')
                        ->columns(1)
                        ->schema([
                            DatePicker::make('data_cancelamento')
                                ->label('Data'),
                            MarkdownEditor::make('motivo_cancelamento')
                                ->label('Motivo'),
                        ])
                ])
                    ->disabled()
                    ->columnSpan(4),
                Group::make([
                    Repeater::make('produtos_na_ordem')
                        ->relationship('produtos_na_ordem')
                        ->label('Produtos')
                        ->defaultItems(0)
                        ->required()
                        ->itemLabel(fn ($state) => (empty($state['quantidade']) || empty($state['produto_id']) ) ? null : ($state['quantidade'] . 'x ' . Produto::query()->find($state['produto_id'])->nome))
                        ->live()
                        ->afterStateUpdated(function (Set $set, $state) {
                            if(empty($state) || array_values($state)[0]['produto_id'] === null) {
                                $set('mapa_producao', '');
                                return;
                            }
                            $etapas = [];

                            foreach ($state as $s){
                                if(empty($s['produto_id'])) continue;
                                $etapas = array_merge($etapas, ProdutoController::getEtapasMapeadas(Produto::query()->find($s['produto_id']))->toArray());
                            }

                            $etapas = array_unique($etapas, SORT_REGULAR);
                            $diagraph = ProdutoController::getDiagraph($etapas);
                            $imagem = ProdutoController::runDotCommand($diagraph);

                            $set('mapa_producao', $imagem);
                        })
                        ->schema([
                            Group::make([
                                TextInput::make('quantidade')
                                    ->label('Quantidade')
                                    ->columnSpan(1),
                                Select::make('produto_id')
                                    ->label('Produto')
                                    ->relationship('produto', 'nome')
                                    ->live()
                                    ->columnSpan(7),
                            ])
                            ->columns(8)
                        ]),
                    Group::make([
                        Placeholder::make('mapa_producao')
                            ->columnSpan(1)
                            ->label('Mapa de Produção')
                            ->extraAttributes(['style' => 'margin: 0 auto'])
                            ->content(fn ($state) => new HtmlString("<img src='" . $state . "'/>")),
                        Placeholder::make('etapas_na_ordem')
                            ->columnSpan(1)
                            ->label('Etapas de Produção')
                            ->extraAttributes(['style' => 'margin: 0 auto'])
                            ->content(fn ($state) => new HtmlString("<img src='" . $state . "'/>")),
                    ])
                        ->columns(2),
                ])
                    ->columnSpan(8),
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
                        ->date('d/m/Y')
                        ->extraHeaderAttributes(['style'=>'width: 1px']),
                    TextColumn::make('data_final_agendamento')
                        ->label('Final')
                        ->date('d/m/Y')
                        ->extraHeaderAttributes(['style'=>'width: 1px']),
                ]),
                ColumnGroup::make('Produção', [
                    TextColumn::make('data_inicio_producao')
                        ->label('Início')
                        ->date('d/m/Y')
                        ->extraHeaderAttributes(['style'=>'width: 1px']),
                    TextColumn::make('data_final_producao')
                        ->label('Final')
                        ->date('d/m/Y')
                        ->extraHeaderAttributes(['style'=>'width: 1px']),
                ]),
            ])
            ->actionsPosition(Tables\Enums\ActionsPosition::BeforeColumns)
            ->actions([
                OrdemDeProducaoImprimir::make('imprimir'),
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
