<?php

namespace App\Filament\Actions\Table;

use Closure;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;
use Spatie\LaravelPdf\Facades\Pdf;

class OrdemDeProducaoImprimir extends Action
{
    protected string | Htmlable | Closure | null $icon = 'fas-print';
    protected string | array | Closure | null $color = 'primary';
    protected string | Htmlable | Closure | null $label = '';
    protected string | Closure | null $tooltip = 'Imprimir';

    protected function setUp(): void
    {
        parent::setUp();
        $this->action(function ($record){
            $pdf = Pdf::html(view('filament.clusters.producao.pages.pedido', ['ordem' => $record])->render())
                ->margins(4, 4, 4, 4)
                ->download();
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->getBrowsershot()->pdf();
                }, 'name.pdf'
            );
        });
    }

    public function isVisible(): bool
    {
        return $this->getRecord()->status === 'agendada' || $this->getRecord()->status === 'em_producao';
    }
}
