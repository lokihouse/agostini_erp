<?php

namespace App\Filament\Clusters\Sistema\Resources;

use App\Filament\Clusters\Sistema;
use App\Filament\Clusters\Sistema\Resources\EventoResource\Pages;
use App\Filament\Clusters\Sistema\Resources\EventoResource\RelationManagers;
use App\Models\Evento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class EventoResource extends Resource
{
    protected static ?string $model = Evento::class;
    protected static ?string $cluster = Sistema::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'Produção';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(12)
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->label('Nome')
                    ->columnSpan(4)
                    ->required(),
                Forms\Components\Textarea::make('descricao')
                    ->columnSpan(8)
                    ->label('Descrição'),
                Forms\Components\Select::make('empresa_id')
                    ->label('Empresa')
                    ->columnSpan(4)
                    ->hint('É específico para uma empresa?')
                    ->relationship('empresa', 'nome_fantasia'),
                Forms\Components\Select::make('tipo')
                    ->label('Tipo')
                    ->columnSpan(4)
                    ->required()
                    ->options([
                        'producao' => 'Produção',
                        'intervalo' => 'Intervalo',
                        'tempo morto' => 'Tempo Morto',
                    ]),
                Forms\Components\Select::make('credito_debito')
                    ->columnSpan(4)
                    ->label('Crédito/Debito')
                    ->required()
                    ->options([
                        'credito' => 'Crédito',
                        'debito' => 'Débito',
                        'nulo' => 'Nulo',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->extraHeaderAttributes(['style' => 'width: 300px']),
                TextColumn::make('descricao'),
                TextColumn::make('empresa.nome_fantasia')
                    ->extraHeaderAttributes(['style' => 'width: 200px']),
                TextColumn::make('tipo')
                    ->badge()
                    ->color(function($state) {
                        switch ($state) {
                            case 'producao': return 'info';
                            case 'intervalo': return 'gray';
                            case 'tempo morto': return 'danger';
                        }
                    })
                    ->extraCellAttributes(['class' => 'flex'])
                    ->extraHeaderAttributes(['class' => 'w-1']),
                IconColumn::make('credito_debito')
                    ->label('C/D')
                    ->extraHeaderAttributes(['class' => 'w-1'])
                    ->icon(fn (string $state): string => match ($state) {
                        'credito' => 'heroicon-s-plus-circle',
                        'debito' => 'heroicon-s-minus-circle',
                        default => 'heroicon-s-ellipsis-horizontal-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'credito' => 'success',
                        'debito' => 'danger',
                        default => 'gray',
                    })
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEventos::route('/'),
            'create' => Pages\CreateEvento::route('/create'),
            'edit' => Pages\EditEvento::route('/{record}/edit'),
        ];
    }
}
