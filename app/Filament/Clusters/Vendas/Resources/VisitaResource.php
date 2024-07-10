<?php

namespace App\Filament\Clusters\Vendas\Resources;

use App\Filament\Actions\VisitaCancelada;
use App\Filament\Actions\VisitaCheckIn;
use App\Filament\Actions\VisitaCheckOut;
use App\Filament\Actions\VisitaPedido;
use App\Filament\Actions\VisitaRouteTo;
use App\Filament\Clusters\Vendas;
use App\Filament\Clusters\Vendas\Resources\VisitaResource\Pages;
use App\Filament\Clusters\Vendas\Resources\VisitaResource\RelationManagers;
use App\Models\Visita;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VisitaResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Visita::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Vendas::class;
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return parent::form($form)
            ->columns(12)
            ->schema([
                Forms\Components\Group::make([
                    Forms\Components\DatePicker::make('data')
                        ->columnSpanFull()
                        ->disabled(),
                    Forms\Components\Select::make('cliente_id')
                        ->relationship('cliente', 'nome_fantasia')
                        ->columnSpanFull()
                        ->disabled(),
                    Select::make('status')
                        ->disabled()
                        ->options([
                            'agendada' => 'Agendada',
                            'iniciada' => 'Iniciada',
                            'finalizada' => 'Finalizada',
                            'cancelada' => 'Cancelada',
                        ])
                        ->columnSpanFull(),
                    Forms\Components\Select::make('user_id')
                        ->relationship('responsavel', 'name', function ($query) {
                            $query->where('empresa_id', auth()->user()->empresa_id);
                        })
                        ->columnSpanFull()
                        ->disabled(fn($record) => $record->user_id !== null),
                    ])
                    ->columnSpan(3),
                Forms\Components\Group::make([
                    Forms\Components\TextInput::make('motivo')
                        ->label('Motivo do cancelamento')
                        ->columnSpanFull()
                        ->disabled(),
                    Forms\Components\Textarea::make('observacao_cancelamento')
                        ->label('Observações')
                        ->rows(5)
                        ->columnSpanFull()
                        ->disabled(),
                ])
                    ->hidden(fn($record) => $record->status !== 'cancelada')
                    ->columnSpan(3),
                Forms\Components\Group::make([
                    Map::make('localizacao')
                        ->mapControls([
                            'mapTypeControl'    => false,
                            'scaleControl'      => false,
                            'streetViewControl' => false,
                            'rotateControl'     => false,
                            'fullscreenControl' => false,
                            'searchBoxControl'  => false, // creates geocomplete field inside map
                            'zoomControl'       => false,
                        ])
                        ->height('304px')
                        ->label('Localização')
                        ->defaultLocation(fn ($record) => array_values($record->cliente->localizacao))
                        ->draggable(false)
                        ->columnSpanFull(),
                ])
                    ->hidden(fn($record) => $record->status === 'cancelada')
                    ->columnSpan(3),
                Forms\Components\Group::make([
                    Forms\Components\Textarea::make('observacao_inicial')
                        ->rows(3),
                ])
                    ->hidden(fn($record) => $record->status === 'cancelada')
                    ->columnSpan(3),
                Forms\Components\Group::make([
                    Forms\Components\Textarea::make('observacao_final')
                        ->rows(3),
                ])
                    ->hidden(fn($record) => $record->status === 'cancelada')
                    ->columnSpan(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->defaultSort('data', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('data')
                    ->date('d/m/Y')
                    ->badge()
                    ->color(function (string $state): string {
                        if(strtotime($state) < strtotime('now')) return 'danger';
                        elseif (strtotime($state) < strtotime('+7 days')) return 'warning';
                        else return 'success';
                    })
                    ->extraHeaderAttributes(['style' => 'width: 120px;']),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(function($state) {
                        switch ($state) {
                            case 'agendada': return 'gray';
                            case 'iniciada': return 'info';
                            case 'finalizada': return 'success';
                            case 'cancelada': return 'danger';
                        }
                    })
                    ->extraHeaderAttributes(['style' => 'width: 120px;']),
                Tables\Columns\TextColumn::make('cliente.nome_fantasia'),
                Tables\Columns\TextColumn::make('cliente.endereco_completo'),
                Tables\Columns\TextColumn::make('responsavel.name')
                    ->label('Responsável'),
            ])
            ->actionsPosition(Tables\Enums\ActionsPosition::BeforeCells)
            ->actions([
                VisitaCheckOut::make('check_out'),
                VisitaPedido::make('realizar_pedido'),
                VisitaCheckIn::make('check_in'),
                VisitaRouteTo::make('como_chegar'),
                VisitaCancelada::make('cancelar')
                    ->hidden(fn($record) => $record->status === 'cancelada'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisitas::route('/'),
            'create' => Pages\CreateVisita::route('/create'),
            'edit' => Pages\EditVisita::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        $defaultPermissions = config('filament-shield.permission_prefixes.resource');
        return array_merge($defaultPermissions, [
            'check_in' => 'check_in',
            'cancelar' => 'cancelar',
        ]);
    }
}
