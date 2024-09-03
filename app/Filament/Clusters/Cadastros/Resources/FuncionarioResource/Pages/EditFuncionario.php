<?php

namespace App\Filament\Clusters\Cadastros\Resources\FuncionarioResource\Pages;

use App\Filament\Actions\Form\FuncionarioAtivar;
use App\Filament\Actions\Form\FuncionarioDesativar;
use App\Filament\Clusters\Cadastros\Resources\FuncionarioResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFuncionario extends EditRecord
{
    protected static string $resource = FuncionarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            FuncionarioAtivar::make('ativar'),
            FuncionarioDesativar::make('desativar'),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);
        $data['roles'] = User::query()->find($data['id'])->roles->first()->name;
        return $data;
    }
}
