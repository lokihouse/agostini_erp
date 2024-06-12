<?php

namespace App\Livewire;

use App\Http\Controllers\VisitaController;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Redirect;

class VisitasDias extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                app(VisitaController::class)->proximosDias()
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
                    Tables\Actions\Action::make('Check in')
                        ->icon('heroicon-o-building-storefront'),
                    Tables\Actions\Action::make('Como Chegar')
                        ->icon('heroicon-o-map')
                        ->action(function($record) {
                            Redirect::away("https://www.google.com.br/maps/dir/" . $record->cliente->localizacao['lat'] . "," . $record->cliente->localizacao['lng']);
                        })
                ])->icon('heroicon-m-ellipsis-horizontal')
            ]);
    }
}
