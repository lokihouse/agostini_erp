<?php

namespace App\Filament\Resources\JornadaDeTrabalhoResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UsuariosRelationManager extends RelationManager
{
    protected static string $relationship = 'usuarios';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nome')
            ->columns([
                TextColumn::make('nome'),
                TextColumn::make('username'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('vincularUsuario')
                    ->modalWidth(MaxWidth::ExtraSmall)
                    ->form([
                        Forms\Components\Select::make('usuario')
                            ->searchable()
                            ->preload()
                            ->options(fn () => User::query()->tenant()->pluck('nome', 'id'))
                    ])
                    ->action(function ($data, $record) {
                        $jornada_de_trabalho_id = $this->getOwnerRecord()->id;
                        $usuario = User::query()->find($data['usuario']);
                        $usuario->jornada_de_trabalho_id = $jornada_de_trabalho_id;
                        $usuario->save();
                    })
                    ->label('Vicular Usuário'),
            ])
            ->actionsPosition(Tables\Enums\ActionsPosition::BeforeColumns)
            ->actions([
                Tables\Actions\DeleteAction::make()->iconButton(),
            ]);
    }
}
