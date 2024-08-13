<?php

namespace App\Filament\Actions;

use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class OrdemDeProducaoCancelar extends Action
{
    protected string | Htmlable | Closure | null $label = '';
    protected string | Closure | null $icon = 'heroicon-o-trash';
    protected string | Closure | null $tooltip = 'Cancelar Ordem de Produção';
    protected string | array | Closure | null $color = 'danger';
    protected MaxWidth | string | Closure | null $modalWidth = 'sm';

    protected function setUp(): void
    {
        $this->form([
            RichEditor::make('motivo')->required(),
        ]);
        $this->requiresConfirmation();
        $this->action(function ($record, $data) {
            $record->update([
                'status' => 'cancelada',
                'motivo_cancelamento' => $data['motivo'],
            ]);
            Notification::make()
                ->title('Ordem Cancelada')
                ->success()
                ->send();
        });
    }

    function isHidden(): bool
    {
        return $this->getRecord()->status === 'finalizada' || $this->getRecord()->status === 'cancelada';
    }
}
