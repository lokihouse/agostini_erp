<?php

namespace App\Filament\Clusters\Sistema\Resources;

use App\Filament\Clusters\Sistema;
use App\Filament\Clusters\Sistema\Resources\EmpresaResource\Pages\CreateEmpresa;
use App\Filament\Clusters\Sistema\Resources\EmpresaResource\Pages\EditEmpresa;
use App\Filament\Clusters\Sistema\Resources\EmpresaResource\Pages\ListEmpresas;
use App\Filament\ResourceBase;
use App\Models\Empresa;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class EmpresaResource extends ResourceBase
{
    protected static ?string $model = Empresa::class;
    protected static ?string $cluster = Sistema::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return parent::form($form)->schema([
            TextInput::make('nome')
                ->required()
                ->columnSpan(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->defaultSort('nome', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable()
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmpresas::route('/'),
            'create' => CreateEmpresa::route('/create'),
            'edit' => EditEmpresa::route('/{record}/edit'),
        ];
    }
}
