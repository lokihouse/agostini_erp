<?php

namespace App\Filament\Actions;

use App\Http\Controllers\PlanoDeContaController;
use App\Models\MovimentacaoFinanceira;
use App\Models\PlanoDeConta;
use App\Models\Visita;
use App\Utils\MyNumberFormater;
use App\Utils\TextFormater;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Actions\Action;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PlanoDeContaCriarNovoSubitem extends Action
{
    protected string | Closure | null $defaultView = 'filament-actions::button-action';
    protected MaxWidth | string | Closure | null $modalWidth = 'md';
    protected function setUp(): void
    {
        $this->form([
            Select::make('plano_de_conta_id')
                ->label('Plano de Conta Raiz')
                ->searchable()
                ->preload()
                ->options( function ($record) {
                    $options = [];
                    $planos = PlanoDeConta::query()
                        ->where('plano_de_conta_id', $record->id)
                        ->where('movimentacao', false)
                        ->pluck(DB::raw('concat(codigo, " - ", descricao)'), 'id')
                        ->toArray();

                    foreach ($planos as $plano_id => $value) {
                        $options[$plano_id] = $value;

                        $planos_1 = PlanoDeConta::query()
                            ->where('plano_de_conta_id', $plano_id)
                            ->where('movimentacao', false)
                            ->pluck(DB::raw('concat(codigo, " - ", descricao)'), 'id')
                            ->toArray();

                        foreach ($planos_1 as $plano_id => $value) {
                            $options[$plano_id] = $value;

                            $planos_2 = PlanoDeConta::query()
                                ->where('plano_de_conta_id', $plano_id)
                                ->where('movimentacao', false)
                                ->pluck(DB::raw('concat(codigo, " - ", descricao)'), 'id')
                                ->toArray();

                            foreach ($planos_2 as $plano_id => $value) {
                                $options[$plano_id] = $value;
                            }

                        }
                    }
                    return $options;
                    }
                )
                ->getOptionLabelFromRecordUsing(fn (PlanoDeConta $record) => "{$record->codigo} - {$record->descricao}"),
            TextInput::make('descricao')
                ->label('Descrição')
                ->required(),
            Group::make([
                Toggle::make('movimentacao')
                    ->label('Aceita Movimentação')
                    ->live()
                    ->inline(false)
                    ->default(false),
                TextInput::make('valor_projetado')
                    ->label('Valor Previsto')
                    ->mask(RawJs::make('$money($input, \',\', \'.\')'))
                    ->stripCharacters('.')
                    ->dehydrateStateUsing(fn ($state) => MyNumberFormater::fromMoney('R$ ' . $state) ?? 0)
                    ->required()
                    ->visible(fn(Get $get, $state, $record) => $get('movimentacao')),
            ])->columns(2)
        ]);

        $this->action(function($data) {
            $data['empresa_id'] = auth()->user()->empresa_id;
            $data['movimentacao'] = $data['movimentacao'] == 'true';
            $data['status'] = $this->getRecord()->status;

            if(empty($data['plano_de_conta_id'])) {
                $data['plano_de_conta_id'] = $this->getRecord()->id;
                $data['codigo'] = PlanoDeContaController::getNextCodigo($this->getRecord());
            }else{
                $plano = PlanoDeConta::find($data['plano_de_conta_id']);
                $data['codigo'] = PlanoDeContaController::getNextCodigo($plano);
            }

            $planoDeConta = new PlanoDeConta();
            $planoDeConta->fill($data);

            $planoDeConta->save();

            Notification::make()->title('Plano de contas criado com sucesso!')->success()->send();
        });
    }
}
