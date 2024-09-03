<?php

namespace App\Filament\Actions\Table;

use Closure;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;

class OrdemDeProducaoCancelar extends Action
{
    protected string | Htmlable | Closure | null $icon = 'fas-trash-can';
    protected string | array | Closure | null $color = 'danger';
    protected string | Htmlable | Closure | null $label = '';
    protected string | Closure | null $tooltip = 'Cancelar';

    protected function setUp(): void
    {
        parent::setUp();
        $this->requiresConfirmation();
        $this ->modalHeading('Cancelar Ordem de Produção');
        $this->modalDescription('Você tem certeza de que deseja cancelar esta ordem de produção? Essa ação não pode ser desfeita.');
        $this->form([
            MarkdownEditor::make('motivo')
                ->label('Motivo do cancelamento')
                ->required(),
        ]);
        $this->action(function ($data, $record){
            $record->update([
                'status' => 'cancelada',
                'motivo_cancelamento' => $data['motivo'],
                'data_cancelamento' => today()
            ]);
            Notification::make('atualizada')
                ->title('Ordem de Produção cancelada')
                ->success()
                ->send();
        });
    }

    public function isHidden(): bool
    {
        return $this->getRecord()->status === 'finalizada' || $this->getRecord()->status === 'cancelada';
    }
}
