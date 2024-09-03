<?php

namespace App\Filament\Clusters\Cadastros\Resources;

use App\Filament\Clusters\Cadastros;
use App\Filament\Clusters\Cadastros\Resources\EquipamentoResource\Pages;
use App\Filament\Clusters\Cadastros\Resources\EquipamentoResource\RelationManagers;
use App\Models\Equipamento;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EquipamentoResource extends Resource
{
    protected static ?string $model = Equipamento::class;
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $cluster = Cadastros::class;
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(12)
            ->schema([
                Group::make([
                    Select::make('departamento_id')
                        ->label('Departamento')
                        ->relationship('departamento', 'nome')
                        ->native(false)
                        ->columnSpan(2)
                        ->required(),
                    TextInput::make('nome')->columnSpan(10),
                    MarkdownEditor::make('descricao')
                        ->columnSpanFull(),
                ])
                    ->columns(12)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('departamento.nome')
                    ->extraHeaderAttributes(['style' => 'width: 200px']),
                TextColumn::make('nome')
                    ->extraHeaderAttributes(['style' => 'width: 200px']),
                TextColumn::make('descricao')
            ])
            ->filters([
                //
            ])
            ->actionsPosition(Tables\Enums\ActionsPosition::BeforeColumns)
            ->actions([]);
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
            'index' => Pages\ListEquipamentos::route('/'),
            'create' => Pages\CreateEquipamento::route('/create'),
            'edit' => Pages\EditEquipamento::route('/{record}/edit'),
        ];
    }
}
