<?php

namespace App\Utils;

class WorkShiftCalculator
{
    public static function timeToMinutes(?string $time): int
    {
        if (empty($time)) {
            return 0;
        }
        try {
            $carbonTime = \Carbon\Carbon::createFromFormat('H:i:s', $time);
            return $carbonTime->hour * 60 + $carbonTime->minute;
        } catch (\Exception $e) {
            try {
                $carbonTime = \Carbon\Carbon::createFromFormat('H:i', $time);
                return $carbonTime->hour * 60 + $carbonTime->minute;
            } catch (\Exception $e2) {
                return 0;
            }
        }
    }

    public static function calculateDurationInMinutes(?string $startTime, ?string $endTime): int
    {
        if (empty($startTime) || empty($endTime)) {
            return 0;
        }

        $startMinutes = self::timeToMinutes($startTime);
        $endMinutes = self::timeToMinutes($endTime);

        if ($endMinutes >= $startMinutes) {
            return $endMinutes - $startMinutes;
        } else {
            // Cruzou a meia-noite
            return (24 * 60 - $startMinutes) + $endMinutes;
        }
    }

    /**
     * Calcula a duração líquida de trabalho em minutos (descontando o intervalo).
     */
    public static function calculateNetWorkDuration(
        ?string $startsAt,
        ?string $endsAt,
        ?string $intervalStartsAt,
        ?string $intervalEndsAt
    ): int {
        $grossDuration = self::calculateDurationInMinutes($startsAt, $endsAt);
        $intervalDuration = 0;

        if (!empty($intervalStartsAt) && !empty($intervalEndsAt)) {
            $startShiftMinutes = self::timeToMinutes($startsAt);
            $endShiftMinutes = self::timeToMinutes($endsAt);
            $startIntervalMinutes = self::timeToMinutes($intervalStartsAt);
            $endIntervalMinutes = self::timeToMinutes($intervalEndsAt);

            $isIntervalWithinShift = false;
            if ($endShiftMinutes >= $startShiftMinutes) {
                if ($startIntervalMinutes >= $startShiftMinutes && $endIntervalMinutes <= $endShiftMinutes && $endIntervalMinutes > $startIntervalMinutes) {
                    $isIntervalWithinShift = true;
                }
            } else {if ($startIntervalMinutes >= $startShiftMinutes && $endIntervalMinutes <= (24*60) && $endIntervalMinutes > $startIntervalMinutes) {
                    $isIntervalWithinShift = true;
                }
                else if ($startIntervalMinutes >= 0 && $endIntervalMinutes <= $endShiftMinutes && $endIntervalMinutes > $startIntervalMinutes) {
                    $isIntervalWithinShift = true;
                }
                // Cenário 3: Intervalo cruza meia-noite (mais complexo, simplificando por agora)
                // Para simplificar, vamos assumir que o intervalo não cruza a meia-noite se o turno cruzar.
                // Uma validação mais robusta seria necessária aqui.
            }

            if ($isIntervalWithinShift) {
                $intervalDuration = self::calculateDurationInMinutes($intervalStartsAt, $intervalEndsAt);
            } else if (!empty($intervalStartsAt) || !empty($intervalEndsAt)) {
                // Se o intervalo foi preenchido mas é inválido, podemos considerar 0 ou adicionar um erro específico.
                // Por ora, vamos considerar 0 para não quebrar o cálculo de netWork,
                // mas um erro de validação deveria ser adicionado para o intervalo em si.
                // Ex: $validator->errors()->add("caminho.para.interval_starts_at", "Intervalo fora do horário de trabalho.");
            }
        }
        return max(0, $grossDuration - $intervalDuration);
    }
}
