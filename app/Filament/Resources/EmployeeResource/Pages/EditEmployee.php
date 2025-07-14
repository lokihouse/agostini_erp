<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);
        $user = User::find($data['user_id']);

        $data = $user->toArray();
        $data['user_id'] = $user->uuid;
        $data['roles'] = $user->getRoleNames()->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = parent::mutateFormDataBeforeSave($data);
        $user = User::find($data['user_id']);
        $user->is_active = $data['is_active'];
        $user->name = $data['name'];
        $user->username = $data['username'];
        $user->work_shift_id = $data['work_shift_id'];
        if(isset($data['password'])){
            $user->password = $data['password'];
        }
        $user->save();
        $user->syncRoles($data['roles']);
        return $data;
    }
}
