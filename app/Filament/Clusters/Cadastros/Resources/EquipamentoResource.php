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
            ->schema([
                Group::make([
                    Group::make([
                        TextInput::make('nome')
                            ->required()
                            ->columnSpanFull()
                            ->placeholder('Nome do equipamento'),
                        Select::make('departamento_id')
                            ->relationship('departamento', 'nome')
                            ->columnSpanFull()
                            ->required(),
                        TextInput::make('posicoes_de_trabalho')
                            ->numeric()
                            ->required()
                            ->default(1),
                    ])->columns(2)->columnSpan(3),
                    MarkdownEditor::make('descricao')
                        ->columnSpan(7)
                        ->placeholder('Descrição do departamento')
                ])->columns(10)->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('departamento.nome')
                    ->searchable()
                    ->extraHeaderAttributes(['style' => 'width: 200px']),
                Tables\Columns\TextColumn::make('nome')
                    ->searchable()
                    ->extraHeaderAttributes(['style' => 'width: 200px']),
                Tables\Columns\TextColumn::make('descricao')
                    ->limit(80)
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
            'index' => Pages\ListEquipamentos::route('/'),
            'create' => Pages\CreateEquipamento::route('/create'),
            'edit' => Pages\EditEquipamento::route('/{record}/edit'),
        ];
    }
}
