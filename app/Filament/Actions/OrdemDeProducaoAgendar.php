<?php

namespace App\Filament\Actions;

use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;

class OrdemDeProducaoAgendar extends Action
{
    protected string | Htmlable | Closure | null $label = '';
    protected string | Closure | null $icon = 'heroicon-o-calendar-days';
    protected string | Closure | null $tooltip = 'Agendar Ordem de Produção';
    protected string | array | Closure | null $color = 'info';
    protected MaxWidth | string | Closure | null $modalWidth = 'sm';

    protected function setUp(): void
    {
        $this->form([
            DatePicker::make('previsao_inicio'),
            DatePicker::make('previsao_final'),
        ]);

        $this->action(function ($record, $data) {

            $record->update([
                'status' => 'agendada',
                'previsao_inicio' => $data['previsao_inicio'],
                'previsao_final' => $data['previsao_final'],
            ]);

            Notification::make()
                ->title('Ordem Agendada')
                ->success()
                ->send();
        });
    }

    function isHidden(): bool
    {
        return $this->getRecord()->status !== 'rascunho';
    }
}
