<?php

namespace App\Filament\Resources\JornadaDeTrabalhoResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HorariosDeTrabalhoRelationManager extends RelationManager
{
    protected static string $relationship = 'horarios_de_trabalho';

    public function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                TextInput::make('dia_do_ciclo')
                    ->numeric()
                    ->minValue(1)
                    // ->maxValue(fn($record) => $record->jornada_de_trabalho->dias_de_ciclo)
                    ->required()
                    ->maxLength(255),
                TimePicker::make('entrada')
                    ->seconds(false),
                TimePicker::make('saida')
                    ->seconds(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordClasses(fn ($record) => $record->dia_do_ciclo > $record->jornada_de_trabalho->dias_de_ciclo ? 'bg-red-100' : null)
            ->columns([
                Tables\Columns\TextColumn::make('dia_do_ciclo')->width(1)->alignCenter(),
                Tables\Columns\TextColumn::make('entrada')->width(1)->alignCenter(),
                Tables\Columns\TextColumn::make('saida')->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Novo horário'),
            ])
            ->actionsPosition(Tables\Enums\ActionsPosition::BeforeColumns)
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ]);
    }
}
