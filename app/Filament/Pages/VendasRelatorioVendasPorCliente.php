<?php

namespace App\Filament\Pages;

use App\Models\PedidoDeVenda;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class VendasRelatorioVendasPorCliente extends Page implements HasTable
{
    use InteractsWithTable;

    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'Relatório de Vendas por Cliente';
    protected ?string $heading = '';
    protected static ?string $navigationGroup = 'Vendas';
    protected static ?int $navigationSort = 39;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.vendas-relatorio-vendas-por-cliente';

    public $startDate;
    public $endDate;

    public function __construct()
    {
        $this->startDate = Carbon::now()->startOfMonth()->subMonths(5);
        $this->endDate = Carbon::now()->endOfMonth();
    }

    public function table(Table $table): Table
    {
        $columns = [
            TextColumn::make('cliente.nome_fantasia'),
        ];

        for($date = Carbon::parse($this->startDate)->clone(); $date->lte(Carbon::parse($this->endDate)); $date->addMonth()) {
            $columns[] = TextColumn::make($date->translatedFormat('M-Y'))->width(100);
        }

        return $table
            ->query(function() {
                $query = PedidoDeVenda::query()->with('cliente');
                dd($query->get()->toArray());
            })
            ->columns($columns)
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }

    public function previousMonthAction(): Action
    {
        return Action::make('previousMonth')
            ->extraAttributes(['class' => 'w-full'])
            ->requiresConfirmation()
            ->icon('heroicon-o-arrow-left')
            ->label('Mês Anterior')
            ->action(function () {
                //
            });
    }

    public function nextMonthAction(): Action
    {
        return Action::make('nextMonth')
            ->extraAttributes(['class' => 'w-full'])
            ->requiresConfirmation()
            ->icon('heroicon-o-arrow-right')
            ->iconPosition(IconPosition::After)
            ->label('Mês Anterior')
            ->action(function () {
                //
            });
    }
}
