<?php

namespace App\Filament\Actions;

use App\Http\Controllers\PlanoDeContaController;
use App\Models\PlanoDeConta;
use App\Models\Visita;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;

class PlanoDeContasAtivar extends Action
{
    protected bool | Closure $isLabelHidden = true;
    protected string | Closure | null $icon = 'heroicon-o-check-badge';
    protected string | Closure | null $defaultView = 'filament-actions::button-action';
    protected MaxWidth | string | Closure | null $modalWidth = 'sm';
    protected string | Closure | null $tooltip = 'Ativar Plano de Contas';

    protected function setUp(): void
    {
        $this->requiresConfirmation();
        $this->action(function($data) {
            $planoDeConta = $this->getRecord();
            PlanoDeContaController::ativarPlanoDeConta($planoDeConta);
        });
    }

    function isHidden(): bool
    {
        if($this->getRecord()->status) return true;

        $hasNewer = PlanoDeConta::where('id', '>', $this->getRecord()->id)
            ->where('empresa_id', $this->getRecord()->empresa_id)
            ->where('plano_de_conta_id', null)
            ->count();

        return !!$hasNewer;
    }
}
