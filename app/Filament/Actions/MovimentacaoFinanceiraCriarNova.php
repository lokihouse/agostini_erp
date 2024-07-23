<?php

namespace App\Filament\Actions;

use App\Models\MovimentacaoFinanceira;
use App\Models\PlanoDeConta;
use App\Models\Visita;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Actions\Action;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;

class MovimentacaoFinanceiraCriarNova extends Action
{
    protected string | Closure | null $defaultView = 'filament-actions::button-action';
    protected MaxWidth | string | Closure | null $modalWidth = 'md';
    protected function setUp(): void
    {
        $this->form([
            Select::make('plano_de_conta_id')
                ->label('Plano de Conta')
                ->searchable()
                ->preload()
                ->relationship(
                    name:'planoDeConta',
                    modifyQueryUsing: fn (Builder $query) => $query->where('status', true)->where('movimentacao', true)->orderBy('codigo'),
                )
                ->getOptionLabelFromRecordUsing(fn (PlanoDeConta $record) => "{$record->codigo} - {$record->descricao}")
                ->required(),
            Group::make([
                Select::make('natureza')
                    ->label('Natureza')
                    ->options([
                        'credito' => 'Crédito',
                        'debito' => 'Débito',
                    ])
                    ->required(),
                TextInput::make('valor')
                    ->mask(RawJs::make('$money($input, \',\', \'.\')'))
                    ->stripCharacters('.')
                    ->required(),
            ])->columns(2),
            Textarea::make('descricao')
                ->label('Descrição')
                ->required()
                ->columnSpanFull(),
        ]);

        $this->action(function($data) {
            $data['empresa_id'] = auth()->user()->empresa_id;
            $movimentacao = new MovimentacaoFinanceira();
            $movimentacao->fill($data);
            // $movimentacao->save();

            dd($movimentacao->toArray());

            Notification::make()->title('Movimentação financeira criada com sucesso!')->success()->send();
        });
    }
}
