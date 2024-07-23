<?php

namespace App\Filament\Clusters\Cadastros\Pages;

use App\Filament\Clusters\Cadastros;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;

class CadastrosEmpresa extends Page implements HasForms
{
    use HasPageShield;
    use InteractsWithForms;

    protected ?string $heading = 'Empresa';
    protected static ?string $navigationLabel = 'Empresa';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string $view = 'filament.clusters.cadastros.pages.empresa';
    protected static ?string $cluster = Cadastros::class;

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = \App\Models\Empresa::query()->where('id', Auth::user()->empresa_id)->first()->toArray();
        $this->data['horarios'] = json_decode($this->data['horarios'], true);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('update')
                ->label('Atualizar')
                ->disabled(fn () => !Auth::user()->can('update_empresa'))
                ->action(function() {
                    // dd($this->data);
                })
        ];
    }

    public function form(Form $form): Form
    {
        $form = \App\Filament\Clusters\Sistema\Resources\EmpresaResource::form($form);
        $form->getComponents(true)[0]->getChildComponents()[0]->getChildComponents()[0]->getChildComponents()[0]->columnSpan(4);
        $form->getComponents(true)[0]->getChildComponents()[0]->getChildComponents()[1]->getChildComponents()[0]->columnSpan(5);
        $form->getComponents(true)[0]->getChildComponents()[0]->getChildComponents()[1]->getChildComponents()[1]->columnSpan(5);
        $form->getComponents(true)[0]->getChildComponents()[0]->getChildComponents()[1]->getChildComponents()[2]->columnSpan(5);
        $form->getComponents(true)[0]->getChildComponents()[0]->getChildComponents()[1]->getChildComponents()[3]->columnSpan(3);
        $form->getComponents(true)[0]->getChildComponents()[0]->getChildComponents()[2]->getChildComponents()[1]->columnSpan(9);
        $form->getComponents(true)[0]->getChildComponents()[0]->getChildComponents()[2]->getChildComponents()[2]->columnSpan(2);
        $form->getComponents(true)[0]->getChildComponents()[0]->getChildComponents()[2]->getChildComponents()[3]->columnSpan(4);
        $form->getComponents(true)[0]->getChildComponents()[0]->getChildComponents()[2]->getChildComponents()[4]->columnSpan(4);
        $form->getComponents(true)[0]->getChildComponents()[0]->getChildComponents()[2]->getChildComponents()[5]->columnSpan(8);
        $form->getComponents(true)[0]->getChildComponents()[0]->getChildComponents()[2]->getChildComponents()[6]->columnSpan(2);
        // dd();
        return $form
            ->disabled(fn() => !Auth::user()->can('update_empresa'))
            ->statePath('data');
    }
}
