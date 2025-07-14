<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = parent::mutateFormDataBeforeCreate($data);
        $user = User::create([
            'company_id' => auth()->user()->company_id,
            'name' => $data['name'],
            'username' => $data['username'],
            'is_active' => $data['is_active'],
            'password' => $data['password']
        ])->assignRole($data['roles']);
        return [
            'user_id' => $user->uuid,
            'company_id' => auth()->user()->company_id
        ];
    }
}
