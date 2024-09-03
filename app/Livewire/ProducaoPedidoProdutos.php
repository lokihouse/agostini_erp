<?php

namespace App\Livewire;

use App\Models\OrdemDeProducaoProduto;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class ProducaoPedidoProdutos extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(OrdemDeProducaoProduto::query())
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('quantidade')
                    ->extraHeaderAttributes(['class' => 'w-1'])
                    ->numeric(),
                Tables\Columns\TextColumn::make('produto.nome'),
            ]);
    }

    public function render(): View
    {
        return view('livewire.producao-pedido-produtos');
    }
}
