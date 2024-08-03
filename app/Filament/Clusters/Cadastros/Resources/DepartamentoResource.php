<?php

namespace App\Filament\Clusters\Cadastros\Resources;

use App\Filament\Clusters\Cadastros;
use App\Filament\Clusters\Cadastros\Resources\DepartamentoResource\Pages;
use App\Filament\Clusters\Cadastros\Resources\DepartamentoResource\RelationManagers;
use App\Filament\ResourceBase;
use App\Models\Departamento;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;


class DepartamentoResource extends ResourceBase
{
    protected static ?string $model = Departamento::class;
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $cluster = Cadastros::class;

    public static function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Group::make([
                    TextInput::make('nome')
                        ->columnSpan(3)
                        ->required()
                        ->placeholder('Nome do departamento'),
                    MarkdownEditor::make('descricao')
                        ->columnSpan(7)
                        ->placeholder('Descrição do departamento')
                ])->columns(10)->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->defaultSort('nome','asc')
            ->columns([
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
            'index' => Pages\ListDepartamentos::route('/'),
            'create' => Pages\CreateDepartamento::route('/create'),
            'edit' => Pages\EditDepartamento::route('/{record}/edit'),
        ];
    }
}
