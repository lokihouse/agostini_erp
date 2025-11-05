<?php

namespace App\Filament\Pages;

use App\Models\SalesGoal;
use App\Models\SalesOrder;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select as FormSelect;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class SalesPerformanceReport extends Page implements HasForms
{
    use InteractsWithForms;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static string $view = 'filament.pages.sales-performance-report';
    protected static ?string $title = 'Relatório de Desempenho de Vendas';
    protected static ?string $navigationLabel = 'Desempenho de Vendas';
    protected static ?string $slug = 'sales-performance-report';

    // Para agrupar com SalesGoalResource (se criado com --resource)
    protected static ?string $navigationGroup = 'Vendas';
    protected static ?int $navigationSort = 46;


    public ?string $start_month = null;
    public ?string $end_month = null;
    public ?string $salesperson_id = null;

    public array $reportData = [];
    public array $monthHeaders = [];

    public function mount(): void
    {
        $this->start_month = Carbon::now()->subMonths(5)->startOfMonth()->format('Y-m'); // Últimos 6 meses
        $this->end_month = Carbon::now()->startOfMonth()->format('Y-m');

        $this->form->fill([
            'start_month' => $this->start_month,
            'end_month' => $this->end_month,
            'salesperson_id' => $this->salesperson_id,
        ]);

        $this->generateReportData();
    }

    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('start_month')
                ->label('Mês Inicial')
                ->native(false)
                ->displayFormat('m/Y')
                ->default(Carbon::now()->subMonths(5)->startOfMonth())
                ->required()
                ->reactive()
                ->maxDate(fn (callable $get) => $get('end_month') ? Carbon::parse($get('end_month').'-01') : now()),
            DatePicker::make('end_month')
                ->label('Mês Final')
                ->native(false)
                ->displayFormat('m/Y')
                ->default(Carbon::now()->startOfMonth())
                ->required()
                ->reactive()
                ->minDate(fn (callable $get) => $get('start_month') ? Carbon::parse($get('start_month').'-01') : null)
                ->maxDate(now()),
            FormSelect::make('salesperson_id')
                ->label('Vendedor (Opcional)')
                ->options(User::where('company_id', auth()->user()->company_id)->whereHas('roles', fn($q) => $q->where('name', 'Vendedor'))->pluck('name', 'uuid'))
                ->searchable()
                ->preload()
                ->nullable()
                ->reactive(),
        ];
    }

    public function submitFilters(): void
    {
        $data = $this->form->getState();
        $this->start_month = Carbon::parse($data['start_month'])->format('Y-m');
        $this->end_month = Carbon::parse($data['end_month'])->format('Y-m');
        $this->salesperson_id = $data['salesperson_id'];
        $this->generateReportData();
    }

    protected function generateReportData(): void
    {
        $startDate = Carbon::parse($this->start_month . '-01')->startOfMonth();
        $endDate = Carbon::parse($this->end_month . '-01')->endOfMonth();

        $this->monthHeaders = [];
        $period = CarbonPeriod::create($startDate, '1 month', $endDate);
        foreach ($period as $date) {
            $this->monthHeaders[] = $date->copy();
        }

        $salespeopleQuery = User::query()
            ->where('company_id', auth()->user()->company_id)
            ->whereHas('roles', fn(Builder $q) => $q->whereIn('name', ['Vendedor', 'Administrador'])) // Ajuste as roles
            ->orderBy('name');

        if ($this->salesperson_id) {
            $salespeopleQuery->where('uuid', $this->salesperson_id);
        }
        $salespeople = $salespeopleQuery->get();

        $report = [];

        $validSaleStatuses = [
            SalesOrder::STATUS_DELIVERED,
            SalesOrder::STATUS_SHIPPED,
            SalesOrder::STATUS_PROCESSING,
            SalesOrder::STATUS_APPROVED,
        ];

        foreach ($salespeople as $salesperson) {
            $salespersonData = [
                'name' => $salesperson->name,
                'uuid' => $salesperson->uuid,
                'months' => [],
                'totals' => ['sales' => 0, 'goal' => 0]
            ];

            foreach ($this->monthHeaders as $monthDate) {
                $monthKey = $monthDate->format('Y-m');
                $monthStart = $monthDate->copy()->startOfMonth();
                $monthEnd = $monthDate->copy()->endOfMonth();

                // MODIFICAÇÃO PRINCIPAL AQUI: Buscar a meta ativa para o período
                $activeGoal = SalesGoal::where('user_id', $salesperson->uuid)
                    // ->where('company_id', $salesperson->company_id) // O TenantScope já deve cuidar disso
                    ->where('period', '<=', $monthStart->toDateString()) // A meta deve ter iniciado em ou antes deste mês
                    ->orderBy('period', 'desc') // Pega a mais recente que se aplica
                    ->first(); // Pega o registro completo da meta

                $goalAmount = $activeGoal ? (float)$activeGoal->goal_amount : 0;
                // FIM DA MODIFICAÇÃO PRINCIPAL

                // Buscar Vendas Realizadas (lógica existente)
                $salesAmount = SalesOrder::where('user_id', $salesperson->uuid)
                    // ->where('company_id', $salesperson->company_id) // TenantScope
                    ->whereIn('status', $validSaleStatuses)
                    ->whereBetween('order_date', [$monthStart, $monthEnd])
                    ->sum('total_amount');
                $salesAmount = (float)$salesAmount;

                $performance = ($goalAmount > 0) ? ($salesAmount / $goalAmount) * 100 : null;
                if ($goalAmount == 0 && $salesAmount > 0) {
                    $performance = 100; // Ou outra lógica para meta não definida mas com vendas
                }

                $salespersonData['months'][$monthKey] = [
                    'sales' => $salesAmount,
                    'goal' => $goalAmount,
                    'performance' => $performance,
                    'difference' => $salesAmount - $goalAmount,
                ];

                $salespersonData['totals']['sales'] += $salesAmount;
                $salespersonData['totals']['goal'] += $goalAmount;
            }
            // Calcular performance total
            $salespersonData['totals']['performance'] = ($salespersonData['totals']['goal'] > 0)
                ? ($salespersonData['totals']['sales'] / $salespersonData['totals']['goal']) * 100
                : ($salespersonData['totals']['sales'] > 0 ? 100 : null);

            $salespersonData['totals']['difference'] = $salespersonData['totals']['sales'] - $salespersonData['totals']['goal'];

            $report[] = $salespersonData;
        }
        $this->reportData = $report;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}

