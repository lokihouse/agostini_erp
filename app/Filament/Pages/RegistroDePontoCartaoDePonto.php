<?php

// app/Filament/Pages/RegistroDePontoCartaoDePonto.php
namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\TimeClockEntry;
use App\Models\Holiday; // Ou seu modelo de Feriados
use App\Models\User;
use App\Models\WorkShift;
use Illuminate\Support\Facades\Log; // Adicionado para logging

class RegistroDePontoCartaoDePonto extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static string $view = 'filament.pages.registro-de-ponto-cartao-de-ponto';
    protected static ?string $title = 'Cartão de Ponto';
    protected static ?string $navigationGroup = 'Ponto Eletrônico';
    protected static bool $shouldRegisterNavigation = false;

    // Propriedades públicas para a view
    public User $currentUser;
    public ?WorkShift $workShift;
    public Carbon $startDate;
    public Carbon $endDate;
    public Carbon $today;
    public array $tabela = [];
    public array $eventosCalendario = [];
    public $allTimeClockEntries;
    public float $cumulativeBalanceSeconds = 0;

     public static function getRelativeRouteName(): string
     {
         return "registro-de-ponto.cartao-de-ponto";
     }

    public function mount(): void
    {
        $this->currentUser = Auth::user()->loadMissing('company', 'workShift.workShiftDays');
        $this->workShift = $this->currentUser->workShift;

        $this->today = Carbon::today();
        $this->startDate = $this->today->copy()->startOfMonth();
        $this->endDate = $this->today->copy()->endOfMonth();

        $this->loadTimeCardData();
    }

    protected function loadTimeCardData(): void
    {
        if (is_null($this->workShift)) {
            Log::info('Usuário ' . $this->currentUser->uuid . ' não possui jornada de trabalho (workShift). Tabela de ponto não será carregada.');
            return;
        }

        if (is_null($this->workShift->workShiftDays)) {
            Log::warning('Usuário ' . $this->currentUser->uuid . ': a relação workShiftDays é nula, mesmo após loadMissing. Verifique a definição da relação no modelo WorkShift.');
            return;
        }


        $this->eventosCalendario = Holiday::query()
            ->where(function ($query) {
                $query->where('company_id', $this->currentUser->company_id)
                    ->orWhereNull('company_id');
            })
            ->where('date', '>=', $this->startDate->format('Y-m-d'))
            ->where('date', '<=', $this->endDate->format('Y-m-d'))
            ->pluck('name', 'date')
            ->all();

        $this->allTimeClockEntries = TimeClockEntry::query()
            ->where('user_id', $this->currentUser->uuid)
            ->whereBetween('recorded_at', [$this->startDate->copy()->startOfDay(), $this->endDate->copy()->endOfDay()])
            ->orderBy('recorded_at')
            ->get();

        $this->cumulativeBalanceSeconds = 0;
        $this->tabela = [];

        for ($date = $this->startDate->copy(); $date->lte($this->endDate); $date->addDay()) {
            if ($date->gt($this->today)) {
                break;
            }

            $obj = [
                'inconsistencia' => false,
                'tipo' => 'util',
                'registros_display' => ['-', '-', '-', '-'],
                'observacao' => [],
                'chd' => 0,
                'cha' => 0,
                'saldo_dia' => 0,
                'saldo_acumulado' => 0,
                'pode_justificar' => true,
            ];

            $dateStringYMD = $date->format('Y-m-d');
            $dayOfWeekCarbon = $date->dayOfWeekIso;

            if ($date->isWeekend()) {
                $obj['tipo'] = 'final_de_semana';
            }
            if (isset($this->eventosCalendario[$dateStringYMD])) {
                $obj["observacao"][] = $this->eventosCalendario[$dateStringYMD];
                $obj['tipo'] = 'feriado';
            }

            // Calcula CHD
            if ($obj['tipo'] !== 'feriado' && $this->workShift->workShiftDays->isNotEmpty()) {
                $workShiftDayConfig = $this->workShift->workShiftDays->firstWhere('day_of_week', $dayOfWeekCarbon);

                if ($workShiftDayConfig && !$workShiftDayConfig->is_off_day) {
                    try {
                        $baseDateForShift = $date->copy()->startOfDay();
                        $entrada = $workShiftDayConfig->starts_at ? $baseDateForShift->copy()->setTimeFromTimeString($workShiftDayConfig->starts_at) : null;
                        $saida = $workShiftDayConfig->ends_at ? $baseDateForShift->copy()->setTimeFromTimeString($workShiftDayConfig->ends_at) : null;
                        $intervalo_inicio = $workShiftDayConfig->interval_starts_at ? $baseDateForShift->copy()->setTimeFromTimeString($workShiftDayConfig->interval_starts_at) : null;
                        $intervalo_fim = $workShiftDayConfig->interval_ends_at ? $baseDateForShift->copy()->setTimeFromTimeString($workShiftDayConfig->interval_ends_at) : null;

                        if ($entrada && $saida) {
                            if ($saida->lt($entrada)) { $saida->addDay(); }
                            if ($intervalo_inicio && $intervalo_fim && $intervalo_fim->lt($intervalo_inicio)) { $intervalo_fim->addDay(); }
                            $diffTotal = $saida->diffInSeconds($entrada, true);
                            $diffIntervalo = 0;
                            $intervalIsValidForCalc = $intervalo_inicio && $intervalo_fim && $intervalo_fim->gt($intervalo_inicio) && $intervalo_inicio->gte($entrada) && $intervalo_fim->lte($saida);
                            if ($intervalIsValidForCalc) {
                                $diffIntervalo = $intervalo_fim->diffInSeconds($intervalo_inicio, true);
                            }
                            $calculatedChd = $diffTotal - $diffIntervalo;
                            $obj['chd'] = $calculatedChd >= 0 ? $calculatedChd : 0;
                        }
                    } catch (\Exception $e) {
                        Log::error("Erro ao calcular CHD para {$this->currentUser->uuid} na data {$dateStringYMD}: " . $e->getMessage(), ['exception' => $e]);
                        $obj['chd'] = 0;
                    }
                }
            }

            // Calcula CHA
            $dayEntries = $this->allTimeClockEntries->filter(
                fn($entry) => Carbon::parse($entry->recorded_at)->isSameDay($date)
            )->values();

            if ($dayEntries->whereIn('status', [TimeClockEntry::STATUS_JUSTIFIED, TimeClockEntry::STATUS_APPROVED])->isNotEmpty()) {
                $obj['pode_justificar'] = false;
            }

            if ($dayEntries->isNotEmpty()) {
                $workedSecondsThisDay = 0;
                $lastClockInTime = null;
                $isOnBreak = false;
                $displayableEntries = [];

                foreach ($dayEntries as $entry) {
                    $entryTime = Carbon::parse($entry->recorded_at);
                    $displayableEntries[] = $entryTime->format('H:i:s'); // Mantido para popular registros_display
                    switch ($entry->type) {
                        case TimeClockEntry::TYPE_CLOCK_IN:
                            if (!$isOnBreak) { $lastClockInTime = $entryTime; }
                            $isOnBreak = false;
                            break;
                        case TimeClockEntry::TYPE_START_BREAK:
                            if ($lastClockInTime && !$isOnBreak) {
                                // GARANTIR 'true' AQUI
                                $workedSecondsThisDay += $entryTime->diffInSeconds($lastClockInTime, true);
                            }
                            $lastClockInTime = null; $isOnBreak = true;
                            break;
                        case TimeClockEntry::TYPE_END_BREAK:
                            if ($isOnBreak) { $lastClockInTime = $entryTime; $isOnBreak = false; }
                            break;
                        case TimeClockEntry::TYPE_CLOCK_OUT:
                            if ($lastClockInTime && !$isOnBreak) {
                                // GARANTIR 'true' AQUI
                                $workedSecondsThisDay += $entryTime->diffInSeconds($lastClockInTime, true);
                            }
                            $lastClockInTime = null; $isOnBreak = false;
                            break;
                        case TimeClockEntry::TYPE_MANUAL_ENTRY:
                            if (!$isOnBreak) {
                                if ($lastClockInTime) {
                                    // GARANTIR 'true' AQUI
                                    $workedSecondsThisDay += $entryTime->diffInSeconds($lastClockInTime, true);
                                    $lastClockInTime = null;
                                } else {
                                    $lastClockInTime = $entryTime;
                                }
                            }
                            $isOnBreak = false;
                            break;
                    }
                }
                $obj['cha'] = $workedSecondsThisDay > 0 ? $workedSecondsThisDay : 0;

                // Corrigido para usar $displayableEntries e garantir 4 slots
                for ($i = 0; $i < 4; $i++) {
                    $obj['registros_display'][$i] = $displayableEntries[$i] ?? '-';
                }
            }

            // Inconsistências e Saldos (mantidos como antes)
            // ... (resto da sua lógica de inconsistências e saldos) ...
            if ($dayEntries->count() % 2 != 0 && $obj['tipo'] === 'util') {
                $obj['inconsistencia'] = true;
                $obj["observacao"][] = "Número ímpar de batidas.";
            }
            $startBreakCount = $dayEntries->where('type', TimeClockEntry::TYPE_START_BREAK)->count();
            $endBreakCount = $dayEntries->where('type', TimeClockEntry::TYPE_END_BREAK)->count();
            if ($startBreakCount !== $endBreakCount) {
                $obj['inconsistencia'] = true;
                $obj["observacao"][] = "Pausas não correspondem (" . $startBreakCount . "IP/" . $endBreakCount . "FP)";
            }
            if ($dayEntries->where('status', TimeClockEntry::STATUS_ALERT)->isNotEmpty()){
                $obj['inconsistencia'] = true;
                if(!in_array("Número ímpar de batidas.", $obj['observacao']) && !in_array("Pausas não correspondem", $obj['observacao'])){
                    $obj["observacao"][] = "Há batidas em alerta.";
                }
            }
            if ($obj['chd'] > 0 && $obj['cha'] == 0 && $obj['tipo'] === 'util' &&
                $dayEntries->where('status', TimeClockEntry::STATUS_JUSTIFIED)->isEmpty() &&
                $dayEntries->where('status', TimeClockEntry::STATUS_APPROVED)->isEmpty()
            ) {
                $obj['inconsistencia'] = true;
                $obj["observacao"][] = "Ausência não justificada.";
            }

            // Saldos
            if ($obj['tipo'] === 'feriado' && $obj['cha'] > 0) {
                $obj['saldo_dia'] = $obj['cha'];
            } elseif ($obj['tipo'] === 'feriado' && $obj['cha'] == 0) {
                $obj['saldo_dia'] = 0;
            } else {
                $obj['saldo_dia'] = $obj['cha'] - $obj['chd'];
            }

            $this->cumulativeBalanceSeconds += $obj['saldo_dia'];
            $obj['saldo_acumulado'] = $this->cumulativeBalanceSeconds;


            $this->tabela[$dateStringYMD] = $obj;
        }
    }

    public function formatarSaldoCustom(int $totalSeconds): string
    {
        if ($totalSeconds == 0) return "00:00:00";
        $sign = $totalSeconds < 0 ? "-" : "";
        $totalSeconds = abs($totalSeconds);
        $h = floor($totalSeconds / 3600);
        $m = floor(($totalSeconds % 3600) / 60);
        $s = $totalSeconds % 60;
        return sprintf("%s%02d:%02d:%02d", $sign, $h, $m, $s);
    }

    public function tabelaLinhaTipoClasse($tipo): string
    {
        switch ($tipo){
            default: return 'text-gray-900 dark:text-gray-100';
            case 'feriado': return 'bg-blue-50 dark:bg-blue-900 text-blue-700 dark:text-blue-300';
            case 'final_de_semana': return 'bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-400';
        }
    }

    public function tabelaLinhaInconsistenciaClasse($inconsistencia): string
    {
        return boolval($inconsistencia) ? 'font-bold' : '';
    }
}
