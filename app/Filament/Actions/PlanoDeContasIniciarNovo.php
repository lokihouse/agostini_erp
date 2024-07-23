<?php

namespace App\Filament\Actions;

use App\Http\Controllers\PlanoDeContaController;
use App\Models\Empresa;
use App\Models\PlanoDeConta;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class PlanoDeContasIniciarNovo extends Action
{
    protected string | Closure | null $defaultView = 'filament-actions::button-action';
    protected MaxWidth | string | Closure | null $modalWidth = 'sm';
    protected string | Htmlable | Closure | null $label = "Iniciar Novo Plano de Contas";
    protected function setUp(): void
    {
        $this->form([
            /*Toggle::make('replicar_anterior')
                ->label('Replicar Plano de Contas Anterior'),*/
            Group::make([
                DatePicker::make('data_inicio')
                    ->required(),
                DatePicker::make('data_fim')
                    ->required(),
            ])->columns(2)
        ]);

        $this->action(function($data) {
            $hasActivePlanoDeConta = PlanoDeConta::query()
                ->where('empresa_id', Auth::user()->empresa_id)
                ->where('status', true)
                ->count() > 0;

            if($hasActivePlanoDeConta){
                Notification::make('')
                    ->title('Não é possível iniciar um novo plano de contas enquanto houver um plano de contas ativo.')
                    ->danger()
                    ->send();
                return;
            }

            $empresa = Empresa::query()->where('id', Auth::user()->empresa_id)->first();

            $planoDeContas = new PlanoDeConta();
            $planoDeContas->empresa_id = $empresa->id;
            $planoDeContas->codigo = '0';
            $planoDeContas->descricao = 'Plano de Contas - ' . Carbon::parse($data['data_inicio'])->format('d/m/Y') . ' à ' . Carbon::parse($data['data_fim'])->format('d/m/Y');
            $planoDeContas->status = false;
            $planoDeContas->data_inicio = Carbon::parse($data['data_inicio']);
            $planoDeContas->data_fim = Carbon::parse($data['data_fim']);
            $planoDeContas->save();

            Notification::make()
                ->title('Plano de Contas criado com sucesso.')
                ->success()
                ->send();
        });
    }
}
