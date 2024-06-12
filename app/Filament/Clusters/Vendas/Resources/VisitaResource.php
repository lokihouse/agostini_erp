<?php

namespace App\Filament\Clusters\Vendas\Resources;

use App\Filament\Actions\VisitaCancelada;
use App\Filament\Actions\VisitaCheckIn;
use App\Filament\Actions\VisitaRouteTo;
use App\Filament\Clusters\Vendas;
use App\Filament\Clusters\Vendas\Resources\VisitaResource\Pages;
use App\Filament\Clusters\Vendas\Resources\VisitaResource\RelationManagers;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Visita;
use App\Utils\NumberFormater;
use App\Utils\TextFormater;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Redirect;

class VisitaResource extends Resource // implements HasShieldPermissions
{
    protected static ?string $model = Visita::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Vendas::class;

    public static function form(Form $form): Form
    {
        return parent::form($form)
            ->columns(10)
            ->schema([
                Forms\Components\Group::make([
                    Forms\Components\Select::make('cliente_id')
                        ->relationship('cliente', 'nome_fantasia')
                        ->columnSpanFull()
                        ->disabled(),
                    ])->columnSpan(2),
                Forms\Components\Group::make([
                    Select::make('status')
                        ->disabled()
                        ->options([
                            'agendada' => 'Agendada',
                            'realizada' => 'Realizada',
                            'cancelada' => 'Cancelada',
                        ])
                        ->columnSpanFull(),
                ])->columnSpan(2),
                Forms\Components\Group::make([
                ])->columnSpan(6),
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
                            case 'agendada': return 'info';
                            case 'realizada': return 'success';
                            case 'cancelada': return 'danger';
                        }
                    })
                    ->extraHeaderAttributes(['style' => 'width: 120px;']),
                Tables\Columns\TextColumn::make('cliente.nome_fantasia'),
                Tables\Columns\TextColumn::make('cliente.endereco_completo'),
                Tables\Columns\TextColumn::make('user_id')
            ])
            ->actionsPosition(Tables\Enums\ActionsPosition::BeforeCells)
            ->actions([
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
}
