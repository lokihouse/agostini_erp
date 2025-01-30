<?php

namespace App\Filament\Resources\CalendarioResource\Pages;

use App\Filament\Resources\CalendarioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCalendario extends EditRecord
{
    protected static string $resource = CalendarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
