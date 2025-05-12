<?php

namespace App\Filament\Resources\PauseReasonResource\Pages;

use App\Filament\Resources\PauseReasonResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPauseReason extends EditRecord
{
    protected static string $resource = PauseReasonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
