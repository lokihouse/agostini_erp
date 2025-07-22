<?php

namespace App\Filament\Resources\ProductionOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductionOrderLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'productionOrderLogs';

    protected static ?string $title = "Registros de Produção";
    protected static ?string $label = "Registro de Produção";
    protected static ?string $pluralLabel = "Registros de Produção";

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('Registros de Produção')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordTitleAttribute('Registros de produção')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Data')->width(1),
                Tables\Columns\TextColumn::make('user.name')->label('Responsável')->width(1),
                Tables\Columns\TextColumn::make('notes')->label('Registro'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
            ])
            ->bulkActions([
            ]);
    }
}
