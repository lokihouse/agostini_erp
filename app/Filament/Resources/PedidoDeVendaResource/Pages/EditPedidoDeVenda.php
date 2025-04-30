<?php

namespace App\Filament\Resources\PedidoResource\Pages;

use App\Filament\Resources\PedidoDeVendaResource;
use App\Models\OrdemDeProducao;
use App\Models\PedidoDeVenda;
use App\Models\ProdutoPorOrdemDeProducao;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\EditRecord;

class EditPedidoDeVenda extends EditRecord
{
    protected static string $resource = PedidoDeVendaResource::class;

    protected function getFormActions(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Enviar para Produção')
                ->visible(fn($record) => $record->status === 'novo')
                ->requiresConfirmation()
                ->action(function ($data) {
                    $ordemDeProducao = new OrdemDeProducao();
                    $ordemDeProducao->empresa_id = auth()->user()->empresa_id;
                    $ordemDeProducao->cliente_id = $this->record->cliente_id;
                    $ordemDeProducao->status = 'novo';
                    $ordemDeProducao->data_programacao = Carbon::now();
                    $ordemDeProducao->save();

                    foreach ($this->record->produtos as $produto) {
                        $produtoPorOrdemDeProducao = new ProdutoPorOrdemDeProducao();
                        $produtoPorOrdemDeProducao->ordem_de_producao_id = $ordemDeProducao->id;
                        $produtoPorOrdemDeProducao->produto_id = $produto->id;
                        $produtoPorOrdemDeProducao->quantidade = $produto->quantidade;
                        $produtoPorOrdemDeProducao->save();
                    }

                    $this->record->status = 'processado';
                    $this->record->save();
                }),
            Actions\Action::make('Cancelar Pedido')
                ->visible(fn($record) => $record->status === 'novo')
                ->color('danger')
                ->requiresConfirmation()
                ->form([
                    Textarea::make('justificativa')
                ])->action(function ($data) {
                    $this->record->status = 'cancelado';
                    $this->record->justificativa = $data['justificativa'];
                    $this->record->save();
                })
        ];
    }
}
