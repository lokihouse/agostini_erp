<?php

namespace App\Filament\Clusters\Cadastros\Resources\FuncionarioResource\Pages;

use App\Filament\Clusters\Cadastros\Resources\FuncionarioResource;
use App\Filament\Exports\UserExporter;
use App\Filament\Imports\UserImporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

class ListFuncionarios extends ListRecords
{
    protected static string $resource = FuncionarioResource::class;

    public function getTableRecords(): Collection|Paginator|CursorPaginator
    {
        return parent::getTableRecords()->filter(function ($user) {
            return !$user->hasRole('super_admin');
        });
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ImportAction::make()
                ->label('Importar usuários')
                ->iconButton()
                ->icon('heroicon-o-arrow-down-on-square')
                ->importer(UserImporter::class),
            Actions\ExportAction::make()
                ->label('Exportar usuários')
                ->iconButton()
                ->icon('heroicon-o-arrow-up-on-square')
                ->exporter(UserExporter::class),
            Actions\CreateAction::make(),
        ];
    }
}
