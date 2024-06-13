<?php

namespace App\Filament\Clusters\Cadastros\Resources\UserResource\Pages;

use App\Filament\Clusters\Cadastros\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTableRecords(): Collection|Paginator|CursorPaginator
    {
        return parent::getTableRecords()->filter(function ($user) {
            return !$user->hasRole('super_admin');
        });
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
