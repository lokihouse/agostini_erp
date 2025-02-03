<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitaResource\Pages;
use App\Filament\Resources\VisitaResource\RelationManagers;
use App\Models\Visita;
use App\Utils\Cnpj;
use App\Utils\Telefone;
use Filament\Forms;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class VisitaResource extends ResourceBase
{
    protected static ?string $model = Visita::class;
    protected static ?string $navigationGroup = 'Vendas';
    protected static ?int $navigationSort = 30;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                /*Forms\Components\Section::make('Dados do Cliente')
                    ->collapsible()
                    ->compact()
                    ->columnSpan(1)
                    ->schema([
                        Placeholder::make('cnpj')
                            ->content(fn($record) => Cnpj::format($record->cliente['cnpj'])),
                        Placeholder::make('razao_social')
                            ->label('Razão Social')
                            ->content(fn($record) => $record->cliente['razao_social']),
                        Placeholder::make('nome_fantasia')
                            ->label('Nome Fantasia')
                            ->content(fn($record) => $record->cliente['nome_fantasia']),
                        Placeholder::make('telefone')
                            ->label('Telefone')
                            ->content(fn($record) => Telefone::format($record->cliente['telefone']) ?? "-"),
                        Placeholder::make('email')
                            ->label('Email')
                            ->content(fn($record) => $record->cliente['email'] ?? '-'),
                    ]),
                Placeholder::make('Informações')
                    ->visible(fn($record) => $record['status'] === 'agendada')
                    ->content(fn() => 'Visita agendada'),
                Placeholder::make('Informações')
                    ->visible(fn($record) => $record['status'] === 'em andamento')
                    ->content(fn() => 'Visita iniciada, mas ainda não finalizada.'),
                Forms\Components\Section::make('Relatório de Visita')
                    ->visible(fn($state) => $state['status'] === 'realizada' && is_null($state['pedido_de_venda_id']))
                    ->columnSpan(2),
                Forms\Components\Section::make('Pedido')
                    ->visible(fn($state) => $state['status'] === 'realizada' && !is_null($state['pedido_de_venda_id']))
                    ->schema([
                        KeyValue::make('produtos')
                            ->addable(false)
                            ->deletable(false)
                    ])
                    ->columnSpan(2),
                Forms\Components\Section::make('Cancelamento')
                    ->visible(fn($state) => $state['status'] === 'cancelado')
                    ->columnSpan(1)*/
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // ->rowUrl(function ($record) {
            //    return route('filament.app.pages.registro-de-visita', ['id' => $record->id]);
            // })
            ->columns([
                TextColumn::make('data')
                    ->width(300)
                    ->date('j \de F \de Y'),
                TextColumn::make('cliente.nome_fantasia'),
                TextColumn::make('vendedor.nome')->width(300),
                TextColumn::make('status')
                    ->width(1)
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        default => 'gray',
                        'em andamento' => 'warning',
                        'finalizada' => 'info',

                    })
            ])->recordUrl(function ($record) { return route('filament.app.pages.registro-de-visita', ['id' => $record->id]); });
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
            // 'create' => Pages\CreateVisita::route('/create'),
            // 'edit' => Pages\EditVisita::route('/{record}/edit'),
        ];
    }
}
