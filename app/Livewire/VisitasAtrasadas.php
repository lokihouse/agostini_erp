<?php

namespace App\Livewire;

use App\Filament\Actions\VisitaCheckIn;
use App\Filament\Actions\VisitaCheckOut;
use App\Filament\Actions\VisitaRouteTo;
use App\Http\Controllers\VisitaController;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Redirect;

class VisitasAtrasadas extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                app(VisitaController::class)->atrasadas()
            )
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('data')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->extraHeaderAttributes(['class' => 'w-1'])
                ,
                Tables\Columns\TextColumn::make('cliente.nome_fantasia'),
                Tables\Columns\TextColumn::make('cliente.endereco_completo')
                    ->visibleFrom('sm')
            ])
            ->contentGrid([
                'sm' => 4,
                'md' => 1,
            ])
            ->actionsPosition(Tables\Enums\ActionsPosition::BeforeCells)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    VisitaCheckOut::make('check-out')
                        ->label('Check-Out'),
                    VisitaCheckIn::make('check-in')
                        ->label('Check-In'),
                    VisitaRouteTo::make('como_chegar')
                        ->label('Como Chegar')
                ])->icon('heroicon-m-ellipsis-horizontal')
            ]);
    }
}
