<?php

namespace App\Filament\Resources\OrdemDeProducaoResource\Pages;

use App\Filament\Resources\OrdemDeProducaoResource;
use App\Models\OrdemDeProducao;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\HtmlString;

class ListOrdemDeProducaos extends ListRecords
{
    protected static string $resource = OrdemDeProducaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Criar Ordem de Produção')
                ->requiresConfirmation()
                ->modalContent(new HtmlString('<p>Uma nova ordem de produção <b>SEM UM CLIENTE DEFINIDO</b> será criada. Posteriormente você podera adicionar produtos, mas não será possível vincular a um cliente.</p>'))
                ->action(function ($data) {
                    $ordemDeProducao = new OrdemDeProducao([
                        'empresa_id' => auth()->user()->empresa_id,
                        'status' => 'novo',
                        'data_programacao' => date('Y-m-d H:i:s'),
                    ]);
                    $ordemDeProducao->save();
                })
        ];
    }
}
