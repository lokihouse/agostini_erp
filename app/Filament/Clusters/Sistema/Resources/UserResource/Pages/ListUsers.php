<?php

namespace App\Filament\Clusters\Sistema\Resources\UserResource\Pages;

use App\Filament\Clusters\Sistema\Resources\UserResource;
use App\Filament\Exports\UserExporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
