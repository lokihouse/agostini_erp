<?php

namespace App\Filament\Clusters\Financeiro\Resources;

use App\Filament\Actions\PlanoDeContasAtivar;
use App\Filament\Actions\PlanoDeContasFinalizar;
use App\Filament\Clusters\Financeiro;
use App\Filament\Clusters\Financeiro\Resources\PlanoDeContaResource\Pages;
use App\Filament\Clusters\Financeiro\Resources\PlanoDeContaResource\RelationManagers;
use App\Models\PlanoDeConta;
use App\Utils\NumberFormater;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlanoDeContaResource extends Resource
{
    protected static ?string $model = PlanoDeConta::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Financeiro::class;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(12)
            ->schema([
                TextInput::make('descricao')
                    ->label('Descrição')
                    ->columnSpan(3)
                    ->required(),
                Toggle::make('movimentacao')
                    ->label('Movimentação')
                    ->inline(false),
                TextInput::make('valor_projetado')
                    ->label('Valor Previsto')
                    ->columnSpan(2)
                    ->mask(RawJs::make('$money($input, \',\', \'.\')'))
                    ->stripCharacters('.')
                    ->dehydrateStateUsing(fn ($state) => NumberFormater::fromMoney('R$ ' . $state) ?? 0)
                    ->required()
                    ->visible(fn(Get $get, $state, $record) => $get('movimentacao')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('descricao')
                    ->label('Vigência'),
                IconColumn::make('status')
                    ->extraHeaderAttributes(['class' => 'w-1'])
                    ->label('Status')->boolean(),
            ])->actionsPosition(Tables\Enums\ActionsPosition::BeforeCells)
            ->actions([
                PlanoDeContasAtivar::make('ativar'),
                PlanoDeContasFinalizar::make('finalizar'),
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
            'index' => Pages\ListPlanoDeContas::route('/'),
            // 'create' => Pages\CreatePlanoDeConta::route('/create'),
            'view' => Pages\ViewPlanoDeConta::route('/{record}'),
            'edit' => Pages\EditPlanoDeConta::route('/{record}/edit'),
        ];
    }
}
