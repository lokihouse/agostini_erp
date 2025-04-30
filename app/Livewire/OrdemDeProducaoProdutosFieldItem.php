<?php

namespace App\Livewire;

use App\Models\ProdutoPorOrdemDeProducao;
use Livewire\Component;

class OrdemDeProducaoProdutosFieldItem extends Component
{
    public $produto;

    public function mount($produto)
    {
        $this->produto = $produto;
    }
    public function render()
    {
        return view('livewire.ordem-de-producao-produtos-field-item');
    }

    public function deleteProduto(){
        $produtoPorOrdemDeProducao = ProdutoPorOrdemDeProducao::query()->find($this->produto['id']);
        $produtoPorOrdemDeProducao->delete();
        return redirect(request()->header('Referer'));
    }
}
