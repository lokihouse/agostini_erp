<?php

namespace App\Filament\Actions;

use App\Models\User;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\MaxWidth;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Facades\Pdf;

class RegistroDePontoGerarRelatorio extends Action
{
    protected string | Closure | null $defaultView = 'filament-actions::button-action';
    protected MaxWidth | string | Closure | null $modalWidth = 'sm';
    protected string | Closure | null $icon = 'heroicon-o-document-text';
    protected string | array | Closure | null $color = 'gray';

    protected function setUp(): void
    {
        $this
            ->action(function ($data){
                Pdf::view('report.registro_de_ponto', [
                    'origin' => true,
                    ...$data
                ])
                    ->format("a3")
                    ->save(storage_path('app/public/teste.pdf'));
            })
            ->requiresConfirmation()
            ->modalHeading('Relatório de Ponto')
            ->modalDescription('O relatório será gerado conforme os parâmetros selecionados e você será notificado quando ele estiver pronto. Preencha os campos abaixo e clique em Gerar.')
            ->modalSubmitActionLabel('Gerar')
            ->modalIcon('heroicon-o-document-text')
            ->form([
                DateRangePicker::make('intervalo')
                    ->maxDate(Carbon::now()->format('Y-m-d'))
                    ->required(),
                Select::make('funcionarios')
                    ->helperText('Deixe vazio para selecionar todos os funcionários')
                    ->multiple()
                    ->searchable()
                    ->options(User::query()->where('empresa_id', Auth::user()->empresa_id)->pluck('name', 'id')),
            ])
        ;
    }
}
