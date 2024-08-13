<?php

namespace App\Filament\Clusters\Producao\Resources;

use App\Filament\Actions\OrdemDeProducaoAgendar;
use App\Filament\Actions\OrdemDeProducaoCancelar;
use App\Filament\Actions\OrdemDeProducaoImprimir;
use App\Filament\Clusters\Producao;
use App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource\Pages;
use App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource\RelationManagers;
use App\Models\OrdemDeProducao;
use App\Models\Produto;
use App\Tables\Columns\ProgressBarColumn;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class OrdemDeProducaoResource extends Resource
{
    protected static ?string $navigationLabel = 'Ordem de Produção';
    protected static ?string $pluralLabel = 'Ordens de Produção';
    protected static ?string $label = 'Ordem de Produção';
    protected static ?string $model = OrdemDeProducao::class;
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected static ?string $navigationGroup = 'Cadastros';
    protected static ?string $cluster = Producao::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make([
                    TextInput::make('status')
                        ->disabled()
                        ->default('rascunho'),
                    // DatePicker::make('previsao_inicio'),
                    // DatePicker::make('previsao_final'),
                ])
                    ->columnSpan(3),
                Group::make([
                    Repeater::make('produtos')
                        ->defaultItems(0)
                        ->schema([
                            Group::make([
                                Select::make('produto')
                                    ->options(Produto::all()->pluck('nome', 'id'))
                                    ->searchable()
                                    ->columnSpan(10),
                                TextInput::make('quantidade')
                                    ->columnSpan(2),
                            ])->columns(12)
                        ])
                ])
                    ->columnSpan(12)
            ])->columns(18);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('responsavel.name')
                    ->label('Responsável')
                    ->extraHeaderAttributes(['style' => 'width: 200px']),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'rascunho' => 'gray',
                        'agendada' => 'warning',
                        'em_producao' => 'info',
                        'finalizada' => 'success',
                        'cancelada' => 'danger',
                    })
                    ->extraHeaderAttributes(['style' => 'width: 150px']),
                ProgressBarColumn::make('completude')
                    ->label('Produção')
                    ->extraHeaderAttributes(['style' => 'width: 200px']),
                TextColumn::make('previsao_inicio')
                    ->date('d/m/Y')
                    ->extraHeaderAttributes(['style' => 'width: 125px']),
                TextColumn::make('previsao_final')
                    ->date('d/m/Y')
                    ->extraHeaderAttributes(['style' => 'width: 125px']),
                TextColumn::make('data_inicio')
                    ->date('d/m/Y')
                    ->extraHeaderAttributes(['style' => 'width: 125px']),
                TextColumn::make('data_final')
                    ->date('d/m/Y')
                    ->extraHeaderAttributes(['style' => 'width: 125px']),
            ])
            ->actionsPosition(Tables\Enums\ActionsPosition::BeforeCells)
            ->actions([
                OrdemDeProducaoAgendar::make('agendar'),
                OrdemDeProducaoImprimir::make('imprimir'),
                OrdemDeProducaoCancelar::make('cancelar')
            ]);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrdemDeProducaos::route('/'),
            'create' => Pages\CreateOrdemDeProducao::route('/create'),
            'edit' => Pages\EditOrdemDeProducao::route('/{record}/edit'),
        ];
    }
}
