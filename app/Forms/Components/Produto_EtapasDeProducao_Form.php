<?php

namespace App\Forms\Components;

use App\Models\ProdutoEtapa;
use App\Models\ProdutoEtapaDestino;
use App\Models\ProdutoEtapaOrigem;
use Barryvdh\Debugbar\Facades\Debugbar;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;

class Produto_EtapasDeProducao_Form extends Field
{
    protected string $view = 'forms.components.produto-etapas-de-producao-form';

    protected function setUp(): void
    {
        parent::setUp();
        $this->registerActions([
            $this->adicionaEtapaAction(),
            $this->excluirEtapaAction()
        ]);
    }

    public function adicionaEtapaAction(): Action
    {
        return Action::make('addStep')
            ->label('Adicionar Etapa')
            ->icon('heroicon-o-plus')
            ->size('xs')
            ->modalHeading('Adicionar Nova Etapa')
            ->steps([
                Step::make('Informações')->schema([
                    TextInput::make('nome')
                        ->label('Nome')
                        ->required(false),
                    Textarea::make('descricao')
                        ->label('Descricao')
                        ->hint(fn($state, $component) => strlen($state) . '/' . $component->getMaxLength())
                        ->maxlength(300)
                        ->live()
                ]),
                Step::make('Insumos e Produção')
                    ->columns(2)
                    ->schema([
                        Repeater::make('insumos')
                            ->addActionLabel('Adicionar Insumo')
                            ->columns(4)
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->collapsible(true)
                            ->schema([
                                Group::make([
                                    TextInput::make('quantidade')->required()->numeric(),
                                    TextInput::make('descricao')->required()->columnSpan(3),
                                ])->columns(4)->columnSpanFull()
                            ]),
                        Repeater::make('producao')
                            ->addActionLabel('Adicionar Produto')
                            ->columns(4)
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->collapsible(true)
                            ->schema([
                                Group::make([
                                    TextInput::make('quantidade')->required()->numeric(),
                                    TextInput::make('descricao')->required()->columnSpan(3),
                                ])->columns(4)->columnSpanFull()
                            ])
                    ]),
                Step::make('Origem e Destino')->schema([
                    Select::make('produto_etapa_id_origem')
                        ->label('Etapa de origem')
                        ->native(false)
                        ->multiple()
                        ->options(function () {
                            $etapas = ProdutoEtapa::query()->where('produto_id', $this->getRecord()->id)->pluck('nome', 'id')->toArray();
                            $etapas[0] = 'Início';
                            return $etapas;
                        }),
                    Select::make('produto_etapa_id_destino')
                        ->label('Etapa de destino')
                        ->native(false)
                        ->multiple()
                        ->options(function () {
                            $etapas = ProdutoEtapa::query()->where('produto_id', $this->getRecord()->id)->pluck('nome', 'id')->toArray();
                            $etapas[-1] = 'Final';
                            return $etapas;
                        }),
                ]),
            ])
            ->action(function (array $data) {
                $produtoEtapa = new ProdutoEtapa();
                $produtoEtapa->produto_id = $this->getRecord()->id;
                $produtoEtapa->nome = $data['nome'];
                $produtoEtapa->descricao = $data['descricao'] ?? null;
                $produtoEtapa->insumos = json_encode($data['insumos']) ?? null;
                $produtoEtapa->producao = json_encode($data['producao']) ?? null;
                $produtoEtapa->save();

                if ($data['produto_etapa_id_origem'] !== []) {
                    foreach ($data['produto_etapa_id_origem'] as $item) {
                        $produtoEtapaOrigem = new ProdutoEtapaOrigem();
                        $produtoEtapaOrigem->produto_etapa_id = $produtoEtapa->id;
                        $produtoEtapaOrigem->produto_etapa_id_origem = $item === 0 ? null : $item;
                        $produtoEtapaOrigem->save();
                    }
                }

                if ($data['produto_etapa_id_destino'] !== []) {
                    foreach ($data['produto_etapa_id_destino'] as $item) {
                        $produtoEtapaDestino = new ProdutoEtapaDestino();
                        $produtoEtapaDestino->produto_etapa_id = $produtoEtapa->id;
                        $produtoEtapaDestino->produto_etapa_id_destino = $item === "-1" ? null : $item;
                        $produtoEtapaDestino->save();
                    }
                }

                redirect(request()->header('Referer'));
            });
    }

    public function excluirEtapaAction(): Action
    {
        return Action::make('excluirStep')
            ->iconButton()
            ->icon('heroicon-o-trash')
            ->size('xs')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function ($arguments) {
                $id = $arguments['produto_etapa_id'];

                $produto_etapa = ProdutoEtapa::find($id);
                $produto_etapa_origens = $produto_etapa->origens->toArray() ?? [];
                $produto_etapa_destinos = $produto_etapa->destinos->toArray() ?? [];

                foreach ($produto_etapa_origens as $item) ProdutoEtapaOrigem::find($item['id'])->delete();
                foreach ($produto_etapa_destinos as $item) ProdutoEtapaDestino::find($item['id'])->delete();
                $produto_etapa->delete();

                redirect(request()->header('Referer'));
            });
    }
}
