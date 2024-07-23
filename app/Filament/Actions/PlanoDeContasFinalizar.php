<?php

namespace App\Filament\Actions;

use App\Http\Controllers\PlanoDeContaController;
use App\Models\Visita;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;

class PlanoDeContasFinalizar extends Action
{
    protected bool | Closure $isLabelHidden = true;
    protected string | Closure | null $icon = 'heroicon-s-inbox';
    protected string | Closure | null $defaultView = 'filament-actions::button-action';
    protected MaxWidth | string | Closure | null $modalWidth = 'sm';
    protected string | Closure | null $tooltip = 'Finalizar Plano de Contas';

    protected function setUp(): void
    {
        $this->requiresConfirmation();
        $this->action(function($data) {
            $planoDeConta = $this->getRecord();
            PlanoDeContaController::desativarPlanoDeConta($planoDeConta);
        });
    }

    function isHidden(): bool
    {
        return !$this->getRecord()->status;
    }
}
