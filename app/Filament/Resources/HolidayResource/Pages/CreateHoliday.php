<?php

namespace App\Filament\Resources\HolidayResource\Pages;

use App\Filament\Resources\HolidayResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException; // Importar ValidationException

class CreateHoliday extends CreateRecord
{
    protected static string $resource = HolidayResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        if ($user && $user->company_id) {
            $data['company_id'] = $user->company_id;
        } else {
            // Se o usuário não tem uma company_id, não pode criar um evento específico da empresa.
            // Lança uma exceção de validação que será mostrada ao usuário.
            throw ValidationException::withMessages([
                'company_id' => 'Para criar um evento, seu usuário precisa estar associado a uma empresa.',
            ]);
        }
        return $data;
    }
}
