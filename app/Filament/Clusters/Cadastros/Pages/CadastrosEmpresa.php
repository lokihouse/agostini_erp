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
        return $form
            ->disabled(fn() => !Auth::user()->can('update_empresa'))
            ->statePath('data');
    }
}
