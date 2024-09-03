<?php

namespace App\Filament\Clusters\Cadastros\Resources;

use App\Filament\Clusters\Cadastros;
use App\Filament\Clusters\Cadastros\Resources\DepartamentoResource\Pages;
use App\Filament\Clusters\Cadastros\Resources\DepartamentoResource\RelationManagers;
use App\Models\Departamento;
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

class DepartamentoResource extends Resource
{
    protected static ?string $model = Departamento::class;

    protected static ?string $navigationIcon = 'fas-industry';
    protected static ?string $cluster = Cadastros::class;
    protected static ?string $label = 'Departamento';
    protected static ?string $pluralLabel = 'Departamentos';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(12)
            ->schema([
                Group::make([
                    TextInput::make('nome')->columnSpan(12),
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
                TextColumn::make('nome')
                    ->extraHeaderAttributes(['style' => 'width: 200px']),
                TextColumn::make('descricao'),
                TextColumn::make('equipamentos_count')
                    ->label('Equipamentos')
                    ->counts('equipamentos')
                    ->alignCenter()
                    ->extraHeaderAttributes(['style' => 'width: 1px']),

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
            'index' => Pages\ListDepartamentos::route('/'),
            'create' => Pages\CreateDepartamento::route('/create'),
            'edit' => Pages\EditDepartamento::route('/{record}/edit'),
        ];
    }
}
