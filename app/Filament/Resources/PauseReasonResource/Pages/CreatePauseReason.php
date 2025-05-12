<?php

namespace App\Filament\Resources\PauseReasonResource\Pages;

use App\Filament\Resources\PauseReasonResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePauseReason extends CreateRecord
{
    protected static string $resource = PauseReasonResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        if (!$user->hasRole(config('filament-shield.super_admin.name'))) {
            if ($user->company_id) {
                $data['company_id'] = $user->company_id;
            }
        }
        return $data;
    }
}
