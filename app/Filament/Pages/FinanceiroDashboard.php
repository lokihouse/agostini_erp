<?php

namespace App\Filament\Pages;

use App\Filament\Exports\FinanceiroDashboardExporter;
use App\Models\Movimentacao;
use App\Models\PlanoDeConta;
use Carbon\Carbon;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Filament\Actions\ExportAction;

class FinanceiroDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationGroup = 'Financeiro';
    protected static ?int $navigationSort = 30;
    protected static ?string $title = 'Gestão Financeira';
    protected static string $view = 'filament.pages.financeiro-dashboard';

    public $relatorio;
    public \Carbon\Carbon $dataInicial;
    public \Carbon\Carbon $dataFinal;

    public function mount()
    {
        $this->dataInicial = Carbon::now()->startOfMonth()->subMonths(11);
        $this->dataFinal = Carbon::now()->endOfMonth();

        $planoDeContasPrimeiroNivel = PlanoDeConta::query()
            ->whereNull('plano_de_conta_id')
            ->get();

        $this->relatorio = $planoDeContasPrimeiroNivel;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
