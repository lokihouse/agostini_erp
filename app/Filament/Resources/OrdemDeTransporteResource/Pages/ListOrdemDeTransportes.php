<?php

namespace App\Filament\Resources\OrdemDeTransporteResource\Pages;

use App\Filament\Resources\OrdemDeTransporteResource;
use App\Forms\Components\CargasMapaRotaFormField;
use Filament\Actions;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;

class ListOrdemDeTransportes extends ListRecords
{
    protected static string $resource = OrdemDeTransporteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('criarOrdemDeTransporte')
                ->modalWidth(MaxWidth::Full)
                ->form([
                    Group::make([
                        Group::make([
                            Select::make('user_id')
                                ->label('Motorista')
                                ->searchable()
                                ->preload()
                                ->relationship('motorista', 'nome'),
                            TextInput::make('placa_caminhao')
                                ->label('Placa do Caminhão')
                                ->mask('aaa-9*99')
                                ->required(),
                        ]),
                        Repeater::make('entregas')
                            ->defaultItems(0)
                            ->columnSpan(2)
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                Select::make('cliente')
                                    ->relationship('cliente', 'nome_fantasia'),
                                TableRepeater::make('produtos')
                                    ->reorderable(false)
                                    ->defaultItems(0)
                                    ->columns(3)
                                    ->schema([
                                        TextInput::make('quantidade')
                                            ->label('Quant.'),
                                        Select::make('produto')
                                            ->columnSpan(2)
                                            ->relationship('produto', 'nome'),
                                    ])
                            ])
                        ,
                        CargasMapaRotaFormField::make('rota')
                            ->columnSpan(2)

                    ])->columns(5)
                ]),
        ];
    }
}
