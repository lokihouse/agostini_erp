<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanoDeContaResource\Pages;
use App\Filament\Resources\PlanoDeContaResource\RelationManagers;
use App\Models\PlanoDeConta;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlanoDeContaResource extends Resource
{
    protected static ?string $model = PlanoDeConta::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Financeiro';
    protected static ?int $navigationSort = 30;
    protected static ?string $navigationLabel = 'Planos de Contas';
    protected static ?string $pluralModelLabel = 'Planos de Conta';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(12)
            ->schema([
                Select::make('plano_de_conta_id')
                    ->label('Conta Pai')
                    ->relationship('contaPai', 'nome')
                    ->searchable()
                    ->preload()
                    ->columnSpan(3)
                    ->nullable(),
                Select::make('tipo')
                    ->columnSpan(2)
                    ->required()
                    ->label('Tipo da Conta')
                    ->options([
                        'ativo' => 'Ativo',
                        'passivo' => 'Passivo',
                        'receita' => 'Receita',
                        'despesa' => 'Despesa',
                        'patrimonio_liquido' => 'Patrimônio Líquido',
                    ]),
                TextInput::make('nome')
                    ->required()
                    ->columnSpan(7)
                    ->label('Nome da Conta'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('codigo')->label('Código'),
            Tables\Columns\TextColumn::make('nome')->label('Nome'),
            Tables\Columns\TextColumn::make('tipo')->label('Tipo'),
            Tables\Columns\TextColumn::make('contaPai.nome')
                ->label('Conta Pai')
                ->sortable(),
        ])
            ->defaultSort('codigo');
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
            'create' => Pages\CreatePlanoDeConta::route('/create'),
            'edit' => Pages\EditPlanoDeConta::route('/{record}/edit'),
        ];
    }
}
