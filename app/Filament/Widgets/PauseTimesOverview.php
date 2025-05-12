<?php

namespace App\Filament\Widgets;

use App\Models\PauseReason;
use App\Models\TaskPauseLog;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB; // Para usar DB::raw

class PauseTimesOverview extends BaseWidget
{
    protected static ?int $sort = 3; // Ordem no dashboard, depois dos outros
    protected int | string | array $columnSpan = 'full';
    protected ?string $heading = 'Resumo de Tempos de Pausa (Últimos 7 Dias)';

    protected function getStats(): array
    {
        $startDate = Carbon::now()->subDays(7)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Busca os logs de pausa concluídos no período, com o tipo da razão da pausa
        $pauseData = TaskPauseLog::join('pause_reasons', 'task_pause_logs.pause_reason_uuid', '=', 'pause_reasons.uuid')
            ->whereNotNull('task_pause_logs.resumed_at') // Apenas pausas concluídas
            ->whereBetween('task_pause_logs.paused_at', [$startDate, $endDate])
            ->select(
                'pause_reasons.type as pause_type',
                DB::raw('SUM(task_pause_logs.duration_seconds) as total_duration_seconds')
            )
            ->groupBy('pause_reasons.type')
            ->get()
            ->keyBy('pause_type'); // Chaveia pelo tipo para fácil acesso

        $productiveTimeSeconds = $pauseData->get(PauseReason::TYPE_PRODUCTIVE_TIME)['total_duration_seconds'] ?? 0;
        $deadTimeSeconds = $pauseData->get(PauseReason::TYPE_DEAD_TIME)['total_duration_seconds'] ?? 0;
        $mandatoryBreakSeconds = $pauseData->get(PauseReason::TYPE_MANDATORY_BREAK)['total_duration_seconds'] ?? 0;

        return [
            Stat::make('Tempo Produtivo em Pausas', $this->formatSeconds($productiveTimeSeconds))
                ->description('Pausas que contam como tempo de produção (ex: setup, ajuste rápido).')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('success'),

            Stat::make('Tempo Morto (Não Produtivo)', $this->formatSeconds($deadTimeSeconds))
                ->description('Pausas que NÃO contam como tempo de produção (ex: falta de material, manutenção corretiva).')
                ->descriptionIcon('heroicon-m-no-symbol')
                ->color('danger'),

            Stat::make('Pausas Obrigatórias', $this->formatSeconds($mandatoryBreakSeconds))
                ->description('Pausas definidas por lei ou acordo (ex: refeição, café).')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }

    private function formatSeconds(?int $seconds): string
    {
        if (is_null($seconds) || $seconds <= 0) {
            return '0s';
        }
        return CarbonInterval::seconds($seconds)->cascade()->forHumans(['short' => true, 'parts' => 3]);
    }
}

