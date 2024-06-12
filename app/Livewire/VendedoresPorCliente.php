<?php

namespace App\Livewire;

use App\Models\UserCliente;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class VendedoresPorCliente extends BaseWidget
{
    private $cliente_id = null;
    public function mount($cliente_id)
    {
        $this->cliente_id = $cliente_id;
    }
    public function table(Table $table): Table
    {
        return $table
            ->heading('')
            ->query(
                UserCliente::query()
                    ->where('cliente_id', $this->cliente_id)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Vendedor')

            ])
            ->actionsPosition(Tables\Enums\ActionsPosition::BeforeCells)
            ->actions([
                Tables\Actions\Action::make('remover_vendedor')
                    ->label('')
                    ->icon('heroicon-o-trash')
            ])
            ->headerActions([
                Tables\Actions\Action::make('vincular_vendedor')
            ])
            ;
    }
}
