<?php

namespace App\Filament\Actions\Table;

use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;

class OrdemDeProducaoAgendar extends Action
{
    protected string | Htmlable | Closure | null $icon = 'fas-calendar-plus';
    protected string | array | Closure | null $color = 'warning';
    protected string | Htmlable | Closure | null $label = '';
    protected string | Closure | null $tooltip = 'Agendar';

    protected function setUp(): void
    {
        parent::setUp();
        $this->requiresConfirmation();
        $this->modalIcon('fas-calendar-plus');
        $this->color('warning');
        $this ->modalHeading('Agendar Ordem de Produção');
        $this->modalDescription('Defina, abaixo, as datas previstas para início e final desta ordem de produção.');
        $this->modalSubmitActionLabel('Agendar');
        $this->form([
            Group::make([
            DatePicker::make('data_inicio_agendamento')
                ->label('Início'),
            DatePicker::make('data_final_agendamento')
                ->label('Final'),
            ])->columns(2)
        ]);
        $this->action(function ($data, $record){
            $record->update([
                'status' => 'agendada',
                'data_inicio_agendamento' => $data['data_inicio_agendamento'],
                'data_final_agendamento' => $data['data_final_agendamento'],
            ]);
            Notification::make('atualizada')
                ->title('Ordem de Produção cancelada')
                ->success()
                ->send();
        });
    }

    public function isVisible(): bool
    {
        return $this->getRecord()->status === 'rascunho';
    }
}
