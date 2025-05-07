<?php

namespace App\Livewire\TimeClock;

use App\Models\TimeClockEntry;
// WorkShift e WorkShiftDay não são diretamente usados nos novos métodos, mas mantidos para o cálculo de horas
use App\Models\WorkShift;
use App\Models\WorkShiftDay;
use App\Utils\WorkShiftCalculator; // Supondo que você tenha esta classe
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TimeClockManager extends Component
{
    public ?string $userName = null;
    public ?string $scheduledHoursToday = null;
    public ?string $workedHoursToday = null;
    public ?string $workShiftName = null;

    // Novas propriedades para o botão dinâmico
    public string $nextActionType = 'clock_in';
    public string $nextActionLabel = 'Bater Entrada';
    public bool $hasActiveSession = false; // Para saber se há um 'clock_in' sem 'clock_out'

    public function mount()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $this->userName = $user->name;
        $this->loadWorkShiftData($user);
        $this->calculateWorkedHoursTodayAndUpdateAction($user); // Renomeado para clareza
    }

    protected function loadWorkShiftData($user)
    {
        if ($user->workShift) {
            $this->workShiftName = $user->workShift->name;
            $today = Carbon::now();
            $currentDayOfWeek = $today->dayOfWeekIso;

            $workShiftDay = $user->workShift->workShiftDays()
                ->where('day_of_week', $currentDayOfWeek)
                ->first();

            if ($workShiftDay && !$workShiftDay->is_off_day) {
                $netMinutes = WorkShiftCalculator::calculateNetWorkDuration(
                    $workShiftDay->starts_at,
                    $workShiftDay->ends_at,
                    $workShiftDay->interval_starts_at,
                    $workShiftDay->interval_ends_at
                );
                $this->scheduledHoursToday = $this->formatMinutesToHours($netMinutes);
            } elseif ($workShiftDay && $workShiftDay->is_off_day) {
                $this->scheduledHoursToday = 'Dia de Folga';
            } else {
                $this->scheduledHoursToday = 'Não definido';
            }
        } else {
            $this->workShiftName = 'Nenhuma jornada atribuída';
            $this->scheduledHoursToday = 'N/D';
        }
    }

    // Método unificado para calcular horas e determinar a próxima ação
    public function calculateWorkedHoursTodayAndUpdateAction($user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }
        if (!$user) return;

        $todayEntries = TimeClockEntry::where('user_id', $user->uuid)
            ->whereDate('recorded_at', Carbon::today())
            ->orderBy('recorded_at', 'asc')
            ->get();

        $totalWorkedSeconds = 0;
        $lastClockInTime = null;
        $isOnBreak = false;
        // $lastBreakStartTime = null; // Não usado diretamente no cálculo, mas bom para debug

        foreach ($todayEntries as $entry) {
            $entryTime = Carbon::parse($entry->recorded_at);

            switch ($entry->type) {
                case 'clock_in':
                    if (!$isOnBreak) {
                        $lastClockInTime = $entryTime;
                    }
                    $isOnBreak = false;
                    break;
                case 'start_break':
                    if ($lastClockInTime && !$isOnBreak) {
                        $totalWorkedSeconds += $entryTime->diffInSeconds($lastClockInTime);
                        $isOnBreak = true;
                        // $lastBreakStartTime = $entryTime;
                    }
                    $lastClockInTime = null; // Pausa o contador de trabalho
                    break;
                case 'end_break':
                    if ($isOnBreak) {
                        $lastClockInTime = $entryTime; // Retoma a contagem a partir daqui
                        $isOnBreak = false;
                        // $lastBreakStartTime = null;
                    }
                    break;
                case 'clock_out':
                    if ($lastClockInTime && !$isOnBreak) {
                        $totalWorkedSeconds += $entryTime->diffInSeconds($lastClockInTime);
                    }
                    $lastClockInTime = null;
                    $isOnBreak = false;
                    // $lastBreakStartTime = null;
                    break;
            }
        }

        if ($lastClockInTime && !$isOnBreak) {
            $totalWorkedSeconds += Carbon::now()->diffInSeconds($lastClockInTime);
        }

        $this->workedHoursToday = $this->formatSecondsToHours($totalWorkedSeconds);
        $this->determineNextActionState($todayEntries->last());
    }


    protected function determineNextActionState(?TimeClockEntry $lastEntry)
    {
        if (!$lastEntry) {
            $this->nextActionType = 'clock_in';
            $this->nextActionLabel = 'Bater Entrada';
            $this->hasActiveSession = false;
            return;
        }

        switch ($lastEntry->type) {
            case 'clock_in':
                $this->nextActionType = 'start_break'; // Ou 'clock_out'
                $this->nextActionLabel = 'Iniciar Pausa';
                // Poderíamos ter dois botões aqui: "Iniciar Pausa" e "Bater Saída"
                // Por simplicidade, vamos seguir uma sequência.
                $this->hasActiveSession = true;
                break;
            case 'start_break':
                $this->nextActionType = 'end_break';
                $this->nextActionLabel = 'Finalizar Pausa';
                $this->hasActiveSession = true; // Ainda dentro da sessão de trabalho
                break;
            case 'end_break':
                $this->nextActionType = 'clock_out'; // Ou 'start_break' para outra pausa
                $this->nextActionLabel = 'Bater Saída';
                $this->hasActiveSession = true;
                break;
            case 'clock_out':
                $this->nextActionType = 'clock_in';
                $this->nextActionLabel = 'Bater Entrada';
                $this->hasActiveSession = false;
                break;
            default:
                $this->nextActionType = 'clock_in';
                $this->nextActionLabel = 'Bater Entrada';
                $this->hasActiveSession = false;
        }
    }

    protected function formatMinutesToHours($totalMinutes)
    {
        if ($totalMinutes <= 0) {
            return '00:00';
        }
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    protected function formatSecondsToHours($totalSeconds)
    {
        if ($totalSeconds <= 0) {
            return '00:00';
        }
        try {
            return CarbonInterval::seconds($totalSeconds)->cascade()->format('%H:%I');
        } catch (\Exception $e) {
            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            return sprintf('%02d:%02d', $hours, $minutes);
        }
    }

    // O método agora aceita o tipo de ação como parâmetro
    public function redirectToRegisterPointMap(string $actionType)
    {
        // Passa o actionType como parâmetro da rota
        return redirect()->route('time-clock.map-register-point', ['actionType' => $actionType]);
    }

    public function render()
    {
        // Recalcula no render para manter atualizado, se necessário,
        // ou pode ser chamado por polling ou eventos.
        // $this->calculateWorkedHoursTodayAndUpdateAction(Auth::user());
        return view('livewire.time-clock.time-clock-manager');
    }
}
