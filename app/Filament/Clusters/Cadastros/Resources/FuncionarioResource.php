<?php

namespace App\Filament\Clusters\Cadastros\Resources;

use App\Filament\Clusters\Cadastros;
use App\Filament\Clusters\Cadastros\Resources\FuncionarioResource\Pages;
use App\Filament\Clusters\Cadastros\Resources\FuncionarioResource\RelationManagers;
use App\Filament\Clusters\Sistema\Resources\UsuarioResource;
use App\Models\Funcionario;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class FuncionarioResource extends Resource
{
    protected static ?string $model = Funcionario::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $cluster = Cadastros::class;
    protected static ?string $label = 'Funcionário';
    protected static ?string $pluralLabel = 'Funcionários';
    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        $form = UsuarioResource::form($form);
        $form->getComponents(true)[0]->getChildComponents()[0]->hidden();
        return $form;
    }

    public static function table(Table $table): Table
    {
        $table = UsuarioResource::table($table);
        // $table->getColumns(true)['funcao']->hidden();
        return $table;
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
            'index' => Pages\ListFuncionarios::route('/'),
            'create' => Pages\CreateFuncionario::route('/create'),
            'edit' => Pages\EditFuncionario::route('/{record}/edit'),
        ];
    }
}
