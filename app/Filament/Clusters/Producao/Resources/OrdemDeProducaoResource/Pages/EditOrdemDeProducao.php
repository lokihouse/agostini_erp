<?php

namespace App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource\Pages;

use App\Filament\Clusters\Producao\Resources\OrdemDeProducaoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrdemDeProducao extends EditRecord
{
    protected static string $resource = OrdemDeProducaoResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);
        //if($this->data['status'] !== 'rascunho') $this->redirect(route('filament.app.producao.resources.ordem-de-producaos.index'));
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);

        $data['produtos'] = json_decode($data['produtos'], true);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = parent::mutateFormDataBeforeSave($data);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
