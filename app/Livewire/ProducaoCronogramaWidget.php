<?php

namespace App\Livewire;

use App\Models\OrdemDeProducao;
use Carbon\Carbon;
use Closure;
use Filament\Support\Facades\FilamentColor;
use Filament\Widgets\Widget;
use Guava\Calendar\ValueObjects\Event;
use Guava\Calendar\Widgets\CalendarWidget;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class ProducaoCronogramaWidget extends CalendarWidget
{
    protected ?string $locale = 'pt-br';
    protected string | Closure | HtmlString | null $heading = 'Cronograma';
    protected bool $eventClickEnabled = true;
    protected int $firstDay = 0;

    public function getEvents(array $fetchInfo = []): Collection | array
    {
        $eventos = [];

        $ordensDeProducao = OrdemDeProducao::query()
            ->whereIn('status', ['agendada', 'em_producao', 'finalizada', 'cancelada'])
            ->get()->toArray();

        foreach ($ordensDeProducao as $ordemDeProducao) {
            $backgroundColor = match($ordemDeProducao['status']) {
                'agendada' => 'rgb('. FilamentColor::getColors()['warning']['500'] . ')',
                'em_producao' => 'rgb('. FilamentColor::getColors()['info']['500'] . ')',
                'finalizada' => 'rgb('. FilamentColor::getColors()['success']['500'] . ')',
                'cancelada' => 'rgb('. FilamentColor::getColors()['danger']['500'] . ')',
            };

            $startDate = match($ordemDeProducao['status']) {
                'agendada' => $ordemDeProducao['data_inicio_agendamento'],
                'em_producao' => $ordemDeProducao['data_inicio_producao'],
                'finalizada' => $ordemDeProducao['data_inicio_producao'],
                'cancelada' => $ordemDeProducao['data_cancelamento'],
            };

            $endDate = Carbon::parse(match($ordemDeProducao['status']) {
                'agendada' => $ordemDeProducao['data_final_agendamento'],
                'em_producao' => $ordemDeProducao['data_final_producao'] ?? today(),
                'finalizada' => $ordemDeProducao['data_final_producao'],
                'cancelada' => $ordemDeProducao['data_cancelamento'],
            })->addDay();

            $eventos[] = Event::make()
                ->extendedProps(['id' => $ordemDeProducao['id']])
                ->title('#' . $ordemDeProducao['id'] . (($ordemDeProducao['status'] === 'em_producao' && $ordemDeProducao['data_final_agendamento'] <= today()) ? ' - (atrasada)' : ''))
                ->start($startDate)
                ->end($endDate)
                ->backgroundColor($backgroundColor)
                ->allDay();
        }

        return $eventos;
    }

    public function onEventClick(array $info = [], ?string $action = null): void
    {
        $this->redirect(route('filament.app.producao.resources.ordem-de-producaos.edit', $info['event']['extendedProps']['id']));
    }
}
